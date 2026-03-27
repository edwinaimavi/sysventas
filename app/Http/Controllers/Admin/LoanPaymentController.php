<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\CashMovement;
use App\Models\LoanPayment;
use App\Models\Loan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\LoanDisbursement;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\LoanSchedule;
use App\Models\LoanPaymentExpense;




class LoanPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.payments.index')->only('index', 'list');
        $this->middleware('can:admin.payments.store')->only('store');
        $this->middleware('can:admin.payments.update')->only('update');
        $this->middleware('can:admin.payments.destroy')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branchId = session('branch_id');

        if (!$branchId) {
            return redirect()
                ->route('home')
                ->with('error', 'Debes seleccionar una sucursal para ver los pagos.');
        }

        // Subquery: suma de desembolsos COMPLETADOS por préstamo (y sucursal)
        $disbSub = LoanDisbursement::selectRaw('loan_id, SUM(amount) as total_disbursed')
            ->where('status', 'completed')
            ->where('branch_id', $branchId)
            ->groupBy('loan_id');

        // ✅ Préstamos totalmente desembolsados + NO vencidos
        $loans = Loan::select('loans.*', 'ld.total_disbursed')
            ->joinSub($disbSub, 'ld', function ($join) {
                $join->on('ld.loan_id', '=', 'loans.id');
            })
            ->where('loans.branch_id', $branchId)
            ->where('loans.status', 'disbursed')
            ->whereColumn('ld.total_disbursed', '>=', 'loans.amount')
            /*  ->where(function ($q) {
                $q->whereNull('loans.due_date')
                    ->orWhereDate('loans.due_date', '>=', Carbon::today());
            }) */
            ->with('client')
            ->orderBy('loans.id', 'DESC')
            ->get();

        // ⭐ Calcular saldo pendiente por préstamo (total_payable - pagos completados)
        foreach ($loans as $loan) {
            $loan->remaining_balance_calc = LoanSchedule::where('loan_id', $loan->id)
                ->sum(DB::raw('payment - paid_amount'));
        }

        // ✅ AHORA SÍ: filtrar solo los que tienen saldo > 0
        $loans = $loans->filter(function ($loan) {
            return (float) ($loan->remaining_balance_calc ?? 0) > 0.009;
        })->values();

        return view('admin.loan-payments.index', compact('loans'));
    }


    /**
     * 
     * Listado para DataTables (filtrado por sucursal).
     */
    public function list()
    {
        $branchId = session('branch_id');

        $payments = LoanPayment::with([
            'loan.client',  // préstamo + cliente
            'branch',
            'user',
            'expense',
        ])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($payments)
            ->addIndexColumn()

            // Código de pago
            ->editColumn('payment_code', function ($payment) {
                return $payment->payment_code ?? '—';
            })

            // Código del préstamo
            ->addColumn('loan_code', function ($payment) {
                return optional($payment->loan)->loan_code ?? '—';
            })

            // Nombre del cliente
            ->addColumn('client_name', function ($payment) {
                return optional(optional($payment->loan)->client)->full_name ?? '—';
            })

            // Fecha de pago
            ->editColumn('payment_date', function ($payment) {
                return $payment->payment_date
                    ? $payment->payment_date->format('Y-m-d')
                    : '—';
            })

            // Monto formateado
            ->editColumn('amount', function ($payment) {
                return 'S/ ' . number_format($payment->amount, 2);
            })

            // Método de pago
            ->editColumn('method', function ($payment) {
                return $payment->method ? ucfirst($payment->method) : '—';
            })

            // Tipo de pago (full / partial) -> badge
            ->editColumn('payment_type', function ($payment) {
                $type = $payment->payment_type; // full | partial | null

                switch ($type) {
                    case 'full':
                        $badge = 'bg-info ';
                        $label = 'Pago total';
                        $icon  = 'bi-check2-circle';
                        break;

                    case 'partial':
                        $badge = 'bg-warning';
                        $label = 'Pago parcial';
                        $icon  = 'bi-arrow-down-circle';
                        break;

                    default:
                        $badge = 'bg-secondary';
                        $label = 'Sin definir';
                        $icon  = 'bi-question-circle';
                        break;
                }

                return sprintf(
                    '<span class="badge %s text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                <i class="bi %s me-1"></i> %s
            </span>',
                    $badge,
                    $icon,
                    $label
                );
            })

            // Badge de estado
            ->editColumn('status', function ($payment) {
                $map = [
                    'completed' => ['badge' => 'bg-success',   'label' => 'Completado', 'icon' => 'bi-check-circle'],
                    'pending'   => ['badge' => 'bg-warning',   'label' => 'Pendiente',  'icon' => 'bi-hourglass-split'],
                    'reversed'  => ['badge' => 'bg-danger',    'label' => 'Revertido',  'icon' => 'bi-arrow-counterclockwise'],
                ];

                $info = $map[$payment->status] ?? [
                    'badge' => 'bg-secondary',
                    'label' => ucfirst($payment->status),
                    'icon'  => 'bi-question-circle'
                ];

                return sprintf(
                    '<span class="badge %s text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                <i class="bi %s me-1"></i> %s
            </span>',
                    $info['badge'],
                    $info['icon'],
                    $info['label']
                );
            })


            // Badge de estado
            ->editColumn('status', function ($payment) {
                $map = [
                    'completed' => ['badge' => 'bg-success',   'label' => 'Completado', 'icon' => 'bi-check-circle'],
                    'pending'   => ['badge' => 'bg-warning',   'label' => 'Pendiente',  'icon' => 'bi-hourglass-split'],
                    'reversed'  => ['badge' => 'bg-danger',    'label' => 'Revertido',  'icon' => 'bi-arrow-counterclockwise'],
                ];

                $info = $map[$payment->status] ?? [
                    'badge' => 'bg-secondary',
                    'label' => ucfirst($payment->status),
                    'icon'  => 'bi-question-circle'
                ];

                return sprintf(
                    '<span class="badge %s text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                        <i class="bi %s me-1"></i> %s
                    </span>',
                    $info['badge'],
                    $info['icon'],
                    $info['label']
                );
            })

            // Acciones
            ->addColumn('acciones', function ($payment) {
                return view('admin.loan-payments.partials.acciones', [
                    'payment' => $payment,
                ])->render();
            })

            ->rawColumns(['status', 'payment_type', 'acciones'])
            ->make(true);
    }

    public function create()
    {
        //
    }

    // Código secuencial para el pago (uso en store, NO en update)
    private function generatePaymentCode()
    {
        $last = LoanPayment::latest('id')->first();
        $next = $last ? $last->id + 1 : 1;

        return 'PAG-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function generateCode()
    {
        return response()->json([
            'code' => $this->generatePaymentCode()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       
        
        $branchId = session('branch_id');

        if (!$branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        // Generar código
        $request->merge([
            'payment_code' => $this->generatePaymentCode(),
        ]);

        // ✅ Validación
        $data = $request->validate([
            'loan_id'   => 'required|exists:loans,id',

            'payment_code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('loan_payments', 'payment_code'),
            ],

            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',

            'cash_received' => 'nullable|numeric|min:0',
            'cash_change'   => 'nullable|numeric|min:0',

            'payment_type' => 'nullable|in:partial,full',

            'capital'      => 'nullable|numeric|min:0',
            'interest'     => 'nullable|numeric|min:0',
            'late_fee'     => 'nullable|numeric|min:0',

            'method'       => 'nullable|string|max:40',
            'reference'    => 'nullable|string|max:120',

            'receipt_number' => 'nullable|string|max:120',
            'receipt_file'   => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',

            'status'            => 'required|in:completed,pending,reversed',
            'remaining_balance' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string|max:500',
            'expense_description'=> 'nullable|string|max:500',
            //VALIDACION PARA LOS DATOS DE LOS PAGOS ADICIONALES POR COMISION 
            'expense_amount'      => 'nullable|numeric|min:0',
            'expense_type'        => 'nullable|string|max:50',
            'expense_description' => 'nullable|string|max:255',

        ]);

        // Sucursal + user
        $data['branch_id'] = $branchId;
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        // ===========================
        // 📌 DEFINIR GASTO ANTES DE USARLO
        // ===========================
        $expenseData = [
            'expense_amount'      => (float) ($data['expense_amount'] ?? 0),
            'expense_type'        => $data['expense_type'] ?? null,
            'expense_description' => $data['expense_description'] ?? null,
        ];

        // ===========================
        // VALIDACIÓN EFECTIVO
        // ===========================
        $method = strtolower($data['method'] ?? '');

        if ($method === 'cash') {
            $received = (float) ($data['cash_received'] ?? 0);
            $amount   = (float) ($data['amount'] ?? 0);
            $expense  = (float) ($expenseData['expense_amount'] ?? 0);

            $totalToCollect = $amount + $expense;

            if ($received <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'En pagos en efectivo debes ingresar "Pagó con".',
                ], 422);
            }

            if ($received + 0.009 < $totalToCollect) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El monto recibido no cubre el total (cuota + gasto adicional).',
                ], 422);
            }

            $data['cash_change'] = round($received - $totalToCollect, 2);
        } else {
            $data['cash_received'] = null;
            $data['cash_change']   = null;
        }

        // ===========================
        // CARGAR PRÉSTAMO (MISMA SUCURSAL)
        // ===========================
        $loan = Loan::where('id', $data['loan_id'])
            ->where('branch_id', $branchId)
            ->first();

        if (!$loan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El préstamo no pertenece a la sucursal seleccionada.',
            ], 422);
        }

        // ✅ Regla: NO permitir pagar si está vencido
        if ($loan->isExpired()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Este préstamo está vencido. Debes refinanciar antes de registrar pagos.',
            ], 409);
        }

        // Archivo
        if ($request->hasFile('receipt_file')) {
            $path = $request->file('receipt_file')->store('loan-payments', 'public');
            $data['receipt_file'] = $path;
        }
        /* 
        $expenseData = [
            'expense_amount'      => (float) ($data['expense_amount'] ?? 0),
            'expense_type'        => $data['expense_type'] ?? null,
            'expense_description' => $data['expense_description'] ?? null,
        ]; */

        // ❌ estos NO existen en loan_payments
        unset($data['expense_amount'], $data['expense_type'], $data['expense_description']);


        try {
            DB::beginTransaction();

            // 1) Crear pago
            $payment = LoanPayment::create($data);

            // 1.1) Guardar gasto adicional (si existe)
        /*     if (($expenseData['expense_amount'] ?? 0) > 0.009) {
                LoanPaymentExpense::create([
                    'loan_payment_id'    => $payment->id,
                    'branch_id'          => $branchId,
                    'user_id'            => Auth::id(),
                    'expense_amount'     => $expenseData['expense_amount'],
                    'expense_type'       => $expenseData['expense_type'],
                    'expense_description' => $expenseData['expense_description'],
                ]);
            } */


            if ($payment->status === 'completed') {
                // lock del loan (evita doble pago simultáneo)
                $loan = Loan::lockForUpdate()->find($loan->id);

                // ✅ aplicar y obtener desglose
                $breakdown = $this->applyPaymentToSchedules(
                    $loan->id,
                    (float) $payment->amount,
                    $payment->payment_date
                );

                // ✅ guardar capital/interés dentro del pago (para el voucher)
                $payment->capital  = (float) ($breakdown['capital'] ?? 0);
                $payment->interest = (float) ($breakdown['interest'] ?? 0);

                // Mora: si tu sistema la calcula aparte, guárdala aquí.
                // Por ahora respetamos lo que venga del form (o 0).
                $payment->late_fee = (float) ($payment->late_fee ?? 0);

                $payment->save();
            }


            // ===========================
            // 💰 MOVIMIENTO DE CAJA
            // ===========================
            if ($payment->status === 'completed') {

                $cashBox = CashBox::where('branch_id', $branchId)
                    ->where('status', 'open')
                    ->latest()
                    ->first();

                if (! $cashBox) {
                    DB::rollBack();

                    return response()->json([
                        'status'  => 'error',
                        'message' => 'No hay una caja abierta. Debes aperturar caja antes de registrar pagos.',
                        'code'    => 'NO_CASHBOX_OPEN',
                    ], 409);
                }

                // 1️⃣ Ingreso por pago
                CashMovement::create([
                    'cash_box_id'    => $cashBox->id,
                    'branch_id'      => $branchId,
                    'type'           => 'in',
                    'concept'        => 'capital',
                    'amount'         => $payment->amount,
                    'notes'       => $data['notes'] ?? null,
                    'reference_type' => 'loan_payments',
                    'reference_table' => 'loan_payments',
                    'reference_id'    => $payment->id,
                    'user_id'         => Auth::id(),
                ]);

                $expense = null;

                if (($expenseData['expense_amount'] ?? 0) > 0.009) {
                    $expense = LoanPaymentExpense::create([
                        'loan_payment_id'    => $payment->id,
                        'branch_id'          => $branchId,
                        'user_id'            => Auth::id(),
                        'expense_amount'     => $expenseData['expense_amount'],
                        'expense_type'       => $expenseData['expense_type'],
                        'expense_description' => $expenseData['expense_description'],
                    ]);
                }

                // 2️⃣ Ingreso por gasto adicional (SI EXISTE)
                if ($expense) {
                    CashMovement::create([
                        'cash_box_id'     => $cashBox->id,
                        'branch_id'       => $branchId,
                        'type'            => 'in',
                        'concept'         => 'loan_payment_expense',
                        'amount'          => $expense->expense_amount,
                        'notes'       => $expense->expense_description,
                        'reference_type'  => 'loan_payment_expenses',
                        'reference_table' => 'loan_payment_expenses',
                        'reference_id'    => $expense->id,
                        'user_id'         => Auth::id(),
                    ]);
                }
            }



            // ===========================
            // RECALCULAR SALDO DESDE CRONOGRAMA
            // ===========================

            $totalRemaining = LoanSchedule::where('loan_id', $loan->id)
                ->sum(DB::raw('payment - paid_amount'));

            if ($totalRemaining < 0) {
                $totalRemaining = 0;
            }

            $loan->current_balance = $totalRemaining;

            // total pagado
            $loan->total_paid = LoanPayment::where('loan_id', $loan->id)
                ->where('status', 'completed')
                ->sum('amount');

            $loan->save();

            // guardar saldo luego del pago
            $payment->remaining_balance = $loan->current_balance;
            $payment->save();

            // 4) Cambiar estado del préstamo a finished cuando todo esté pagado
            $this->refreshLoanFinishedStatus($loan->id, $branchId);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pago registrado correctamente y cuotas actualizadas.',
                'data'    => $payment,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error creando pago de préstamo: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al registrar el pago.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function applyPaymentToSchedules(int $loanId, float $amount, $payDate): array
    {
        $remaining = round($amount, 2);
        $date = Carbon::parse($payDate)->toDateString();

        $totalInterestPaid = 0.0;
        $totalCapitalPaid  = 0.0;

        // Cuotas ordenadas (primero la más antigua)
        $schedules = LoanSchedule::where('loan_id', $loanId)
            ->orderBy('installment_no')
            ->lockForUpdate()
            ->get();
        if ($schedules->isEmpty()) {
            return [
                'interest' => 0,
                'capital' => 0
            ];
        }

        foreach ($schedules as $sch) {
            if ($remaining <= 0) break;

            $quota = round((float) $sch->payment, 2);
            $paidBefore = round((float) ($sch->paid_amount ?? 0), 2);

            // ya pagada
            if ($paidBefore + 0.009 >= $quota) {
                continue;
            }

            $need = round($quota - $paidBefore, 2);
            $use  = ($remaining >= $need) ? $need : $remaining;

            // ===========================
            // ✅ DESGLOSE: interés primero
            // ===========================
            $schInterest = round((float) ($sch->interest ?? 0), 2);
            $schCapital  = round((float) ($sch->amortization ?? 0), 2); // tu "AMORT." = capital

            // Antes
            $interestPaidBefore = min($paidBefore, $schInterest);
            $capitalPaidBefore  = max(0, $paidBefore - $schInterest);
            if ($capitalPaidBefore > $schCapital) $capitalPaidBefore = $schCapital;

            // Después (simulamos nuevo pagado)
            $paidAfter = round($paidBefore + $use, 2);
            $interestPaidAfter = min($paidAfter, $schInterest);
            $capitalPaidAfter  = max(0, $paidAfter - $schInterest);
            if ($capitalPaidAfter > $schCapital) $capitalPaidAfter = $schCapital;

            // Delta aportado por ESTE pago
            $deltaInterest = round($interestPaidAfter - $interestPaidBefore, 2);
            $deltaCapital  = round($capitalPaidAfter  - $capitalPaidBefore, 2);

            $totalInterestPaid += $deltaInterest;
            $totalCapitalPaid  += $deltaCapital;

            // ===========================
            // ✅ ACTUALIZAR CUOTA
            // ===========================
            $newPaid = $paidAfter;

            if ($newPaid + 0.009 >= $quota) {
                $sch->paid_amount = $quota;
                $sch->status = 'paid';
                $sch->paid_at = $date;
            } elseif ($newPaid > 0) {
                $sch->paid_amount = $newPaid;
                $sch->status = 'partial';
                $sch->paid_at = null;
            } else {
                $sch->paid_amount = 0;
                $sch->status = 'pending';
                $sch->paid_at = null;
            }

            $sch->save();

            $remaining = round($remaining - $use, 2);
        }

        return [
            'interest' => round($totalInterestPaid, 2),
            'capital'  => round($totalCapitalPaid, 2),
        ];
    }



    private function refreshLoanFinishedStatus(int $loanId, int $branchId): void
    {
        $loan = Loan::lockForUpdate()->findOrFail($loanId);

        $remaining = (float) $loan->current_balance;

        // también validamos por cuotas: si no queda ninguna pendiente/parcial
        $hasOpenSchedules = LoanSchedule::where('loan_id', $loanId)
            ->whereIn('status', ['pending', 'partial'])
            ->exists();

        if ($remaining <= 0.009 && !$hasOpenSchedules) {
            $loan->status = 'finished';
        } else {
            // si ya no está pagado, vuelve a disbursed (según tu negocio)
            if ($loan->status === 'finished') {
                $loan->status = 'disbursed';
            }
        }

        $loan->save();
    }





    public function show(LoanPayment $loanPayment)
    {
        //
    }

    public function edit(LoanPayment $loanPayment)
    {
        //
    }

    /**
     * UPDATE (editar pago)
     */
    public function update(Request $request, LoanPayment $loanPayment)
    {
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        // Aseguramos que el pago pertenezca a la sucursal actual
        if ($loanPayment->branch_id !== $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No puedes editar pagos de otra sucursal.',
            ], 403);
        }

        // Validación (similar al store, pero ignorando el payment_code propio)
        $data = $request->validate([
            'loan_id'   => 'required|exists:loans,id',

            'payment_code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('loan_payments', 'payment_code')->ignore($loanPayment->id),
            ],

            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'payment_type' => 'nullable|in:partial,full',

            'capital'      => 'nullable|numeric|min:0',
            'interest'     => 'nullable|numeric|min:0',
            'late_fee'     => 'nullable|numeric|min:0',

            'method'       => 'nullable|string|max:40',
            'reference'    => 'nullable|string|max:120',

            'receipt_number' => 'nullable|string|max:120',
            'receipt_file'   => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',

            'status'            => 'required|in:completed,pending,reversed',
            'remaining_balance' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ], [
            'loan_id.required'   => 'Debes seleccionar un préstamo.',
            'loan_id.exists'     => 'El préstamo seleccionado no es válido.',

            'payment_code.required' => 'El código del pago es obligatorio.',
            'payment_code.unique'   => 'Ese código de pago ya está en uso.',

            'payment_date.required' => 'La fecha de pago es obligatoria.',
            'payment_date.date'     => 'La fecha de pago no es válida.',

            'amount.required'       => 'El monto es obligatorio.',
            'amount.numeric'        => 'El monto debe ser numérico.',
        ]);

        // Mantener sucursal fija
        $data['branch_id'] = $branchId;

        // Actualizamos user_id (último que modificó)
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        // Validar que el nuevo loan_id también corresponda a la sucursal
        $loan = Loan::where('id', $data['loan_id'])
            ->where('branch_id', $branchId)
            ->first();

        if (! $loan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El préstamo no pertenece a la sucursal seleccionada.',
            ], 422);
        }

        // Manejo del archivo de comprobante (reemplazo)
        if ($request->hasFile('receipt_file')) {
            $path = $request->file('receipt_file')->store('loan-payments', 'public');

            // Borramos el archivo anterior si existía
            if ($loanPayment->receipt_file) {
                Storage::disk('public')->delete($loanPayment->receipt_file);
            }

            $data['receipt_file'] = $path;
        } else {
            // Si no envía archivo nuevo, mantenemos el anterior
            unset($data['receipt_file']);
        }

        try {
            DB::beginTransaction();

            $loanPayment->update($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pago actualizado correctamente.',
                'data'    => $loanPayment->fresh(),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error actualizando pago de préstamo: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al actualizar el pago.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ELIMINAR pago
     */
    /**
     * ANULAR pago (no borrar físicamente)
     */
    public function destroy(Request $request, LoanPayment $loanPayment)
    {
        $branchId = session('branch_id');

        if (!$branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        // ✅ Validar contraseña del usuario logueado
        $user = Auth::user();
        $password = $request->input('password');

        if (!$user || !$password || !Hash::check($password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Contraseña incorrecta. No se puede anular el pago.',
            ], 403);
        }


        // Solo permitir anular pagos de la sucursal actual
        if ($loanPayment->branch_id !== $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No puedes anular pagos de otra sucursal.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $loan = $loanPayment->loan;

            if (!$loan) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El pago no tiene un préstamo asociado.',
                ], 422);
            }

            // 1) Marcar pago como REVERTIDO (no lo borramos)
            $loanPayment->status = 'reversed';

            // ===========================
            // 🔄 REVERSAR MOVIMIENTOS DE CAJA
            // ===========================
            CashMovement::where('reference_id', $loanPayment->id)
                ->where('reference_type', LoanPayment::class)
                ->update([
                    'type' => 'out', // salida compensatoria
                    'concept' => 'reversal',
                ]);


            // Opcional: dejar una nota automática
            $extraNote = ' [Pago anulado]';
            if ($loanPayment->notes) {
                if (strpos($loanPayment->notes, $extraNote) === false) {
                    $loanPayment->notes .= $extraNote;
                }
            } else {
                $loanPayment->notes = 'Pago anulado desde el sistema.';
            }

            $loanPayment->save();

            // 2) Recalcular total pagado (solo pagos COMPLETED)
            $loan->total_paid -= $loanPayment->amount;
            $loan->current_balance += $loanPayment->amount;

            $loan->save();

            $remaining = $loan->current_balance;

            // 3) Ajustar estado del préstamo según saldo
            if ($remaining <= 0.009) {
                // Sigue o vuelve a quedar cancelado
                $loan->status = 'finished';
            } else {
                // Hay saldo pendiente -> préstamo vuelve a "desembolsado"
                $loan->status = 'disbursed';
            }
            $loan->save();

            // 4) Ajustar TIPO DE PAGO del último pago COMPLETED
            $lastCompleted = LoanPayment::where('loan_id', $loan->id)
                ->where('branch_id', $branchId)
                ->where('status', 'completed')
                ->orderBy('payment_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastCompleted) {
                if ($remaining <= 0.009) {
                    // Si ya no hay saldo: el último pago debe ser "Pago total"
                    $lastCompleted->payment_type = 'full';
                } else {
                    // Si hay saldo pendiente: el último pago debe ser parcial
                    $lastCompleted->payment_type = 'partial';
                }
                $lastCompleted->save();
            }

            DB::commit();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Pago anulado correctamente.',
                'remaining' => round($remaining, 2),
                'loan_status' => $loan->status,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error anulando pago de préstamo: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'No se pudo anular el pago.',
            ], 500);
        }
    }

    public function receipt(LoanPayment $payment)
    {
        // Carga relaciones necesarias (ajusta si tus nombres cambian)
        $payment->load([
            'loan.client',
            'loan.branch',
            'user',
            'expense',
        ]);

        return view('admin.loan-payments.receipt', compact('payment'));
    }

    public function balance($loanId)
    {
        $branchId = session('branch_id');

        $loan = Loan::where('id', $loanId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $remaining = (float) $loan->current_balance;

        return response()->json([
            'status' => 'success',
            'data' => [
                'loan_id' => $loan->id,
                'total_payable' => (float) $loan->total_payable,
                'remaining_balance' => round($remaining, 2),
            ]
        ]);
    }


    public function schedulesByLoan(Loan $loan)
    {
        $branchId = session('branch_id');

        if (!$branchId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        if ((int)$loan->branch_id !== (int)$branchId) {
            return response()->json([
                'status' => 'error',
                'message' => 'El préstamo no pertenece a la sucursal seleccionada.',
            ], 403);
        }

        $schedules = LoanSchedule::where('loan_id', $loan->id)
            ->orderBy('installment_no')
            ->get()
            ->map(function ($s) {

                $payment = (float) $s->payment;
                $paid    = (float) ($s->paid_amount ?? 0);

                $remaining = $payment - $paid;
                if ($remaining < 0) $remaining = 0;

                return [
                    'id'             => $s->id,
                    'installment_no'  => (int) $s->installment_no,
                    'due_date'        => \Carbon\Carbon::parse($s->due_date)->toDateString(), // ✅ sin Z

                    // ✅ campos que tu JS necesita para no mostrar 0.00
                    'opening_balance' => round((float) $s->opening_balance, 2),
                    'interest'        => round((float) $s->interest, 2),
                    'amortization'    => round((float) $s->amortization, 2),
                    'payment'         => round((float) $s->payment, 2),
                    'closing_balance' => round((float) $s->closing_balance, 2),

                    // ✅ extra útil para pagos
                    'paid_amount'     => round((float) $paid, 2),
                    'remaining'       => round((float) $remaining, 2),
                    'status'          => $s->status, // pending | partial | paid
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $schedules,
        ]);
    }


    public function loansAvailable()
    {
        $branchId = session('branch_id');
        if (!$branchId) {
            return response()->json(['status' => 'error', 'data' => []], 422);
        }

        // subquery desembolsos
        $disbSub = LoanDisbursement::selectRaw('loan_id, SUM(amount) as total_disbursed')
            ->where('status', 'completed')
            ->where('branch_id', $branchId)
            ->groupBy('loan_id');

        $loans = Loan::select('loans.*', 'ld.total_disbursed')
            ->joinSub($disbSub, 'ld', fn($join) => $join->on('ld.loan_id', '=', 'loans.id'))
            ->where('loans.branch_id', $branchId)
            ->where('loans.status', 'disbursed')
            ->whereColumn('ld.total_disbursed', '>=', 'loans.amount')
            /*  ->where(function ($q) {
                $q->whereNull('loans.due_date')
                    ->orWhereDate('loans.due_date', '>=', Carbon::today());
            }) */
            ->with('client')
            ->orderByDesc('loans.id')
            ->get();

        // saldo pendiente
        $data = $loans->map(function ($loan) {

            $remaining = LoanSchedule::where('loan_id', $loan->id)
                ->sum(DB::raw('payment - paid_amount'));

            return [
                'id' => $loan->id,
                'loan_code' => $loan->loan_code,
                'client_name' => optional($loan->client)->full_name ?? '',
                'client_document' => optional($loan->client)->document_number ?? '',
                'remaining_balance' => round($remaining, 2),
                'total_payable' => (float)($loan->total_payable ?? 0),
            ];
        })
            ->filter(fn($x) => (float)$x['remaining_balance'] > 0.009)
            ->values();

        return response()->json(['status' => 'success', 'data' => $data]);
    }
}
