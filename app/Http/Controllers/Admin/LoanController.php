<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Guarantor;
use App\Models\Loan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\LoanSchedule;

use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branchId = session('branch_id');

        // Clientes solo de la sucursal
        $clients = Client::where('status', '!=', -1)
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->orderBy('full_name')
            ->get();

        // Garantes SIN branch_id (globales)
        $guarantors = Guarantor::where('status', '!=', -1)
            ->orderBy('full_name')
            ->get();

        return view('admin.loans.index', compact('clients', 'guarantors'));
    }


    public function list()
    {
        $branchId = session('branch_id');

        $loans = Loan::query()
            ->with(['client', 'guarantor', 'user', 'branch'])
            ->withSum('disbursements as total_disbursed', 'amount')
            ->withCount('refinances')
            ->withExists([
                'refinances as has_active_refinance' => function ($q) {
                    $q->where('status', 'active');
                }
            ])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->get();

        return DataTables::of($loans)
            ->addIndexColumn()

            ->addColumn('client_name', fn($loan) => $loan->client?->full_name ?? '—')
            ->addColumn('guarantor_name', fn($loan) => $loan->guarantor?->full_name ?? '—')

            ->addColumn('has_refinance', function ($loan) {
                $hasHistory = (int)($loan->refinances_count ?? 0) > 0;
                $hasActive  = (bool)($loan->has_active_refinance ?? false);
                return ($hasHistory || $hasActive) ? 1 : 0;
            })
            ->addColumn('is_overdue', fn($loan) => $loan->isExpired() ? 1 : 0)
            ->addColumn('is_finished', fn($loan) => $loan->status === 'finished' ? 1 : 0)

            ->editColumn('amount', fn($loan) => 'S/ ' . number_format((float)$loan->amount, 2))

            ->editColumn('status', function ($loan) {

                $map = [
                    'pending'   => ['badge' => 'bg-warning',   'label' => 'Pendiente',    'icon' => 'bi-hourglass-split'],
                    'approved'  => ['badge' => 'bg-primary',   'label' => 'Aprobado',     'icon' => 'bi-check2-circle'],
                    'rejected'  => ['badge' => 'bg-danger',    'label' => 'Rechazado',    'icon' => 'bi-x-circle'],
                    'disbursed' => ['badge' => 'bg-success',   'label' => 'Desembolsado', 'icon' => 'bi-cash-stack'],
                    'canceled'  => ['badge' => 'bg-secondary', 'label' => 'Cancelado',    'icon' => 'bi-slash-circle'],
                    'finished'  => ['badge' => 'bg-dark',      'label' => 'Finalizado',   'icon' => 'bi-check-circle-fill'],
                ];

                $info = $map[$loan->status] ?? [
                    'badge' => 'bg-secondary',
                    'label' => ucfirst((string)$loan->status),
                    'icon'  => 'bi-question-circle'
                ];

                $hasHistory = (int)($loan->refinances_count ?? 0) > 0;
                $hasActive  = (bool)($loan->has_active_refinance ?? false);
                $hasRef     = $hasHistory || $hasActive;

                $isOverdue  = $loan->isExpired();

                // ✅ CHIPS compatibles con TU CSS (loan-dot-*)
                $chips = '';

                if ($hasRef) {
                    $chips .= '
        <span class="loan-dot loan-dot-ref" title="Refinanciado">
            <span class="dot"></span> Ref.
        </span>
    ';
                }

                if ($isOverdue) {
                    $chips .= '
        <span class="loan-dot loan-dot-over" title="Vencido">
            <span class="dot"></span> Venc.
        </span>
    ';
                }

                /* 👇 placeholder invisible si no hay chips */
                if (!$hasRef && !$isOverdue) {
                    $chips = '<span class="loan-dot-placeholder">.</span>';
                }


                return '
                <div class="loan-status-wrap">
                    <span class="badge ' . $info['badge'] . ' text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                        <i class="bi ' . $info['icon'] . ' me-1"></i> ' . $info['label'] . '
                    </span>
                    ' . $chips . '
                </div>
            ';
            })

            ->addColumn('acciones', function ($loan) {

                $statusOriginal = $loan->status;

                $totalDisbursed   = (float) ($loan->total_disbursed ?? 0);
                $isFullyDisbursed = $totalDisbursed >= (float) $loan->amount;

                $isExpired = $loan->isExpired();

                $hasActiveRefinance = (bool)($loan->has_active_refinance ?? false);
                $canRefinance = $isExpired && !$hasActiveRefinance;

                return view('admin.loans.partials.acciones', compact(
                    'loan',
                    'statusOriginal',
                    'isFullyDisbursed',
                    'totalDisbursed',
                    'canRefinance',
                    'hasActiveRefinance',
                    'isExpired'
                ))->render();
            })

            ->rawColumns(['status', 'acciones'])
            ->make(true);
    }







    //funcion para generar codigo de prestamo
    private function generateLoanCode()
    {
        $last = Loan::orderBy('id', 'DESC')->first();

        if (!$last) {
            return 'CR-0001';
        }

        // Obtener el número del último código
        $num = intval(substr($last->loan_code, 3)) + 1;

        return 'CR-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }


    //funcion para retornar codigo de prestamo
    public function generateCode()
    {
        return response()->json([
            'code' => $this->generateLoanCode()
        ]);
    }

    public function create()
    {
        //
    }




    private function calcFrenchAmortization(float $amount, int $termMonths, float $interestRatePercent): array
    {
        // interestRatePercent = % mensual (ej 20 = 20% mensual)
        $r = $interestRatePercent / 100; // tasa mensual en decimal
        $n = $termMonths;

        if ($amount <= 0 || $n <= 0) {
            return [
                'monthly_payment' => 0,
                'total_payable' => 0,
            ];
        }

        // Si tasa 0% => cuota = monto / meses
        if ($r <= 0) {
            $pmt = $amount / $n;
            return [
                'monthly_payment' => round($pmt, 2),
                'total_payable' => round($pmt * $n, 2),
            ];
        }

        // PMT = P * [ r*(1+r)^n ] / [ (1+r)^n - 1 ]
        $pow = pow(1 + $r, $n);
        $pmt = $amount * (($r * $pow) / ($pow - 1));

        return [
            'monthly_payment' => round($pmt, 2),
            'total_payable' => round($pmt * $n, 2),
        ];
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
                'message' => 'No hay una sucursal seleccionada en la sesión.',
            ], 422);
        }

        $request->merge([
            'loan_code' => $this->generateLoanCode(),
        ]);

        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'guarantor_id'    => 'nullable|exists:guarantors,id',

            'loan_code'       => [
                'required',
                'string',
                'max:30',
                Rule::unique('loans', 'loan_code'),
            ],

            'amount'          => 'required|numeric|min:0.01',
            'term_months'     => 'required|integer|min:1',
            'interest_rate'   => 'required|numeric|min:0|max:100',

            'monthly_payment' => 'nullable|numeric|min:0',
            'total_payable'   => 'nullable|numeric|min:0',

            'disbursement_date' => 'nullable|date',
            'due_date'          => 'nullable|date|after_or_equal:disbursement_date',

            'status'          => 'required|in:pending,approved,rejected,canceled',
            'notes'           => 'nullable|string',
        ], [
            'client_id.required'     => 'Debes seleccionar un cliente.',
            'client_id.exists'       => 'El cliente seleccionado no es válido.',
            'guarantor_id.exists'    => 'El garante seleccionado no es válido.',

            'loan_code.required'     => 'El código del préstamo es obligatorio.',
            'loan_code.unique'       => 'Ese código de préstamo ya está en uso.',

            'amount.required'        => 'El monto es obligatorio.',
            'amount.numeric'         => 'El monto debe ser numérico.',

            'term_months.required'   => 'El plazo en meses es obligatorio.',
            'term_months.integer'    => 'El plazo debe ser un número entero.',

            'interest_rate.required' => 'La tasa de interés es obligatoria.',
        ]);

        // 🔹 Forzamos branch y user desde backend
        $data['branch_id'] = $branchId;

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        // ============================
        // ✅ CÁLCULO SERVER-SIDE (CUOTA FIJA - SISTEMA FRANCÉS)
        // ============================
        $amount       = (float) $data['amount'];
        $termMonths   = (int) $data['term_months'];
        $interestRate = (float) $data['interest_rate']; // % mensual

        $calc = $this->calcFrenchAmortization($amount, $termMonths, $interestRate);

        $data['monthly_payment'] = $calc['monthly_payment'];
        $data['total_payable']   = $calc['total_payable'];

        $data['current_balance'] = $calc['total_payable'];
        $data['total_paid'] = 0;
        $data['is_refinanced'] = 0;
        $data['refinance_count'] = 0;

        // 👇 Cálculo automático de fecha de vencimiento (si no mandan due_date)
        if (!empty($data['disbursement_date']) && empty($data['due_date'])) {
            $data['due_date'] = Carbon::parse($data['disbursement_date'])
                ->addMonths($termMonths)
                ->toDateString();
        }

        try {
            DB::beginTransaction();

            $loan = Loan::create($data);

            $disbDate = $data['disbursement_date'] ?? now()->toDateString();

            $scheduleRows = $this->buildFrenchSchedule(
                (float)$data['amount'],
                (int)$data['term_months'],
                (float)$data['interest_rate'],
                $disbDate
            );

            // Guardar cronograma
            foreach ($scheduleRows as $row) {
                LoanSchedule::create(array_merge($row, [
                    'loan_id' => $loan->id,
                ]));
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Préstamo creado correctamente.',
                'data'    => $loan,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error creando préstamo: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al crear el préstamo.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $loan = Loan::with(['client', 'guarantor', 'user', 'branch'])->findOrFail($id);

        return response()->json($loan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $loan = Loan::findOrFail($id);
        $branchId = session('branch_id');

        // ❌ No permitir editar préstamos finalizados
        if ($loan->status === 'finished') {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se puede editar un préstamo que ya está finalizado.',
            ], 409);
        }

        if (!$branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay una sucursal seleccionada en la sesión.',
            ], 422);
        }

        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'guarantor_id'    => 'nullable|exists:guarantors,id',

            'loan_code'       => [
                'required',
                'string',
                'max:30',
                Rule::unique('loans', 'loan_code')->ignore($loan->id),
            ],

            'amount'          => 'required|numeric|min:0.01',
            'term_months'     => 'required|integer|min:1',
            'interest_rate'   => 'required|numeric|min:0|max:100',

            'monthly_payment' => 'nullable|numeric|min:0',
            'total_payable'   => 'nullable|numeric|min:0',

            'disbursement_date' => 'nullable|date',
            'due_date'          => 'nullable|date|after_or_equal:disbursement_date',

            'status'          => 'required|in:pending,approved,rejected,disbursed,canceled,finished',
            'notes'           => 'nullable|string',
        ], [
            'client_id.required'  => 'Debes seleccionar un cliente.',
            'client_id.exists'    => 'El cliente seleccionado no es válido.',
            'guarantor_id.exists' => 'El garante seleccionado no es válido.',
        ]);

        // 🔹 Forzamos branch y user también en update
        $data['branch_id'] = $branchId;
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        // ============================
        //   REGLAS DE CONSISTENCIA
        // ============================

        // Cargamos suma de desembolsos
        $loan->loadSum('disbursements as total_disbursed', 'amount');
        $totalDisbursed = (float) ($loan->total_disbursed ?? 0);
        $loanAmount     = (float) $loan->amount;

        // 1) Si ya está totalmente desembolsado, NO permitir cambiar a otro estado que no sea disbursed
        if ($totalDisbursed >= $loanAmount && $data['status'] !== 'disbursed') {
            return response()->json([
                'status'  => 'error',
                'message' => 'El préstamo ya está desembolsado y no puede cambiarse a otro estado.',
            ], 409);
        }

        // 2) Si tiene algún desembolso (parcial o total), NO permitir pasarlo a pending/rejected/canceled
        if ($totalDisbursed > 0 && in_array($data['status'], ['pending', 'rejected', 'canceled'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El préstamo ya tiene desembolsos registrados y no puede cambiarse a ese estado.',
            ], 409);
        }

        // ============================
        // ✅ CÁLCULO SERVER-SIDE (SISTEMA FRANCÉS - MISMO QUE STORE)
        // ============================
        $amount       = (float) $data['amount'];
        $termMonths   = (int) $data['term_months'];
        $interestRate = (float) $data['interest_rate']; // % mensual

        $calc = $this->calcFrenchAmortization($amount, $termMonths, $interestRate);

        $data['monthly_payment'] = $calc['monthly_payment'];
        $data['total_payable']   = $calc['total_payable'];

        // 👇 Si no mandan due_date pero sí fecha de desembolso, la recalculamos
        if (!empty($data['disbursement_date']) && empty($data['due_date'])) {
            $data['due_date'] = Carbon::parse($data['disbursement_date'])
                ->addMonths($termMonths)
                ->toDateString();
        }

        try {
            DB::beginTransaction();

            $loan->update($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Préstamo actualizado correctamente.',
                'data'    => $loan,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error actualizando préstamo: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al actualizar el préstamo.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    public function destroy(Loan $loan)
    {
        if ($loan->status === 'finished') {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se puede eliminar un préstamo que ya está finalizado.',
            ], 409);
        }

        try {
            DB::transaction(function () use ($loan) {

                // 🔥 1. Obtener desembolsos
                $disbursements = $loan->disbursements;

                foreach ($disbursements as $d) {

                    // 🔥 2. eliminar movimientos relacionados
                    \App\Models\CashMovement::where('reference_table', 'loan_disbursements')
                        ->where('reference_id', $d->id)
                        ->delete();

                    // 🔥 3. eliminar desembolso (dispara eventos también)
                    $d->delete();
                }

                // 🔥 4. eliminar préstamo
                $loan->delete();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Préstamo eliminado correctamente.'
            ]);
        } catch (\Throwable $e) {

            Log::error("Error eliminando préstamo: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'No se pudo eliminar el préstamo.',
            ], 500);
        }
    }


    private function buildFrenchSchedule(float $amount, int $termMonths, float $interestRatePercent, string $disbursementDate): array
    {
        $r = $interestRatePercent / 100; // mensual decimal
        $n = $termMonths;

        if ($amount <= 0 || $n <= 0) return [];

        // Cuota fija (PMT)
        if ($r <= 0) {
            $pmt = $amount / $n;
        } else {
            $pow = pow(1 + $r, $n);
            $pmt = $amount * (($r * $pow) / ($pow - 1));
        }

        $pmt = round($pmt, 2);

        $balance = round($amount, 2);
        $start = \Carbon\Carbon::parse($disbursementDate);

        $rows = [];

        for ($i = 1; $i <= $n; $i++) {
            $due = $start->copy()->addMonths($i)->toDateString();

            $opening = $balance;

            // interés del periodo
            $interest = ($r <= 0) ? 0 : round($opening * $r, 2);

            // amortización = cuota - interés
            $amort = round($pmt - $interest, 2);

            // ajuste por redondeo en la última cuota para cerrar en 0.00
            if ($i === $n) {
                $amort = $opening;
                $pmtLast = round($amort + $interest, 2);
                $closing = 0;
            } else {
                $pmtLast = $pmt;
                $closing = round($opening - $amort, 2);
                if ($closing < 0) $closing = 0;
            }

            $rows[] = [
                'installment_no'   => $i,
                'due_date'         => $due,
                'opening_balance'  => $opening,
                'interest'         => $interest,
                'amortization'     => $amort,
                'payment'          => $pmtLast,
                'closing_balance'  => $closing,
            ];

            $balance = $closing;
        }

        return $rows;
    }
}
