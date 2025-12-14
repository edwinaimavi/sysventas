<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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


class LoanPaymentController extends Controller
{
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

        // Préstamos totalmente desembolsados
        $loans = Loan::select('loans.*', 'ld.total_disbursed')
            ->joinSub($disbSub, 'ld', function ($join) {
                $join->on('ld.loan_id', '=', 'loans.id');
            })
            ->where('loans.branch_id', $branchId)
            ->where('loans.status', 'disbursed')
            ->whereColumn('ld.total_disbursed', '>=', 'loans.amount')
            ->with('client')
            ->orderBy('loans.id', 'DESC')
            ->get();

        // ⭐ Calcular saldo pendiente por préstamo (total_payable - pagos completados)
        foreach ($loans as $loan) {
            $totalPaid = LoanPayment::where('loan_id', $loan->id)
                ->where('branch_id', $branchId)
                ->where('status', 'completed')
                ->sum('amount');

            $remaining = (float) $loan->total_payable - (float) $totalPaid;
            if ($remaining < 0) {
                $remaining = 0;
            }

            // atributo "virtual" para usar en el blade
            $loan->remaining_balance_calc = round($remaining, 2);
        }

        return view('admin.loan-payments.index', compact('loans'));
    }


    /**
     * Listado para DataTables (filtrado por sucursal).
     */
    public function list()
    {
        $branchId = session('branch_id');

        $payments = LoanPayment::with([
            'loan.client',  // préstamo + cliente
            'branch',
            'user',
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

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay sucursal seleccionada en la sesión.',
            ], 422);
        }

        // Generar código (si quieres seguir haciéndolo acá)
        $request->merge([
            'payment_code' => $this->generatePaymentCode(),
        ]);

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
        ]);

        $data['branch_id'] = $branchId;

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $loan = Loan::where('id', $data['loan_id'])
            ->where('branch_id', $branchId)
            ->first();

        if (! $loan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El préstamo no pertenece a la sucursal seleccionada.',
            ], 422);
        }

        if ($request->hasFile('receipt_file')) {
            $path = $request->file('receipt_file')->store('loan-payments', 'public');
            $data['receipt_file'] = $path;
        }

        try {
            DB::beginTransaction();

            // Crear pago
            $payment = LoanPayment::create($data);

            // Recalcular total pagado
            $loan->refresh();

            $totalPaid = LoanPayment::where('loan_id', $loan->id)
                ->where('branch_id', $branchId)
                ->where('status', 'completed')
                ->sum('amount');

            $remaining = (float) $loan->total_payable - (float) $totalPaid;
            if ($remaining < 0) {
                $remaining = 0;
            }

            $payment->remaining_balance = round($remaining, 2);
            $payment->save();

            if ($remaining <= 0.009) {
                $loan->status = 'finished';
                $loan->save();
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pago registrado correctamente.',
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
            $totalPaid = LoanPayment::where('loan_id', $loan->id)
                ->where('branch_id', $branchId)
                ->where('status', 'completed')
                ->sum('amount');

            $remaining = (float) $loan->total_payable - (float) $totalPaid;
            if ($remaining < 0) {
                $remaining = 0;
            }

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
}
