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

        // Traemos los préstamos de la sucursal con sus relaciones + suma de desembolsos
        $loans = Loan::with(['client', 'guarantor', 'user', 'branch'])
            ->withSum('disbursements as total_disbursed', 'amount')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);   // 👈 sólo préstamos de esta sucursal
            })
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($loans)
            ->addIndexColumn()

            // Nombre del cliente
            ->addColumn('client_name', function ($loan) {
                return $loan->client ? $loan->client->full_name : '—';
            })

            // Nombre del garante (si existe)
            ->addColumn('guarantor_name', function ($loan) {
                return $loan->guarantor ? $loan->guarantor->full_name : '—';
            })

            // Formatear monto
            ->editColumn('amount', function ($loan) {
                return 'S/ ' . number_format($loan->amount, 2);
            })

            // Badge de estado
            ->editColumn('status', function ($loan) {
                $map = [
                    'pending'   => ['badge' => 'bg-warning',   'label' => 'Pendiente',    'icon' => 'bi-hourglass-split'],
                    'approved'  => ['badge' => 'bg-primary',   'label' => 'Aprobado',     'icon' => 'bi-check2-circle'],
                    'rejected'  => ['badge' => 'bg-danger',    'label' => 'Rechazado',    'icon' => 'bi-x-circle'],
                    'disbursed' => ['badge' => 'bg-success',   'label' => 'Desembolsado', 'icon' => 'bi-cash-stack'],
                    'canceled'  => ['badge' => 'bg-secondary', 'label' => 'Cancelado',    'icon' => 'bi-slash-circle'],
                    'finished'  => ['badge' => 'bg-dark',      'label' => 'Finalizado',   'icon' => 'bi-check-circle-fill'], // 👈 NUEVO
                ];

                $info = $map[$loan->status] ?? [
                    'badge' => 'bg-secondary',
                    'label' => ucfirst($loan->status),
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

            // Acciones (ver/editar/eliminar/desembolso)
            ->addColumn('acciones', function ($loan) {
                $statusOriginal   = $loan->status;

                // suma de desembolsos (viene de withSum)
                $totalDisbursed   = (float) ($loan->total_disbursed ?? 0);
                $isFullyDisbursed = $totalDisbursed >= (float) $loan->amount;

                return view('admin.loans.partials.acciones', compact(
                    'loan',
                    'statusOriginal',
                    'isFullyDisbursed',
                    'totalDisbursed'
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $branchId = session('branch_id');

        if (! $branchId) {
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

            // ❌ branch_id ya no se pide del request
            // ❌ user_id tampoco

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
            'client_id.required'    => 'Debes seleccionar un cliente.',
            'client_id.exists'      => 'El cliente seleccionado no es válido.',
            'guarantor_id.exists'   => 'El garante seleccionado no es válido.',

            'loan_code.required'    => 'El código del préstamo es obligatorio.',
            'loan_code.unique'      => 'Ese código de préstamo ya está en uso.',

            'amount.required'       => 'El monto es obligatorio.',
            'amount.numeric'        => 'El monto debe ser numérico.',

            'term_months.required'  => 'El plazo en meses es obligatorio.',
            'term_months.integer'   => 'El plazo debe ser un número entero.',

            'interest_rate.required' => 'La tasa de interés es obligatoria.',
        ]);

        // 🔹 Forzamos branch y user desde backend
        $data['branch_id'] = $branchId;

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        // ============================
        //   CÁLCULO SERVER-SIDE
        // ============================
        $amount       = (float) $data['amount'];
        $termMonths   = (int) $data['term_months'];
        $interestRate = (float) $data['interest_rate']; // tasa en %

        if ($termMonths > 0) {
            // Interés fijo sobre el monto
            $interestAmount = $amount * ($interestRate / 100);

            // Cuota mensual = monto + interés
            $monthly = $amount + $interestAmount;

            // Total = cuota mensual * meses
            $total = $monthly * $termMonths;

            $data['monthly_payment'] = round($monthly, 2);
            $data['total_payable']   = round($total, 2);
        }
        // 👇 Cálculo automático de fecha de vencimiento
        if (!empty($data['disbursement_date']) && empty($data['due_date'])) {
            $data['due_date'] = Carbon::parse($data['disbursement_date'])
                ->addMonths($termMonths)
                ->toDateString();
        }

        try {
            DB::beginTransaction();

            $loan = Loan::create($data);

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

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay una sucursal seleccionada en la sesión.',
            ], 422);
        }

        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'guarantor_id'    => 'nullable|exists:guarantors,id',
            // ❌ branch_id del request no lo usamos
            // ❌ user_id del request tampoco

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
            'client_id.required'    => 'Debes seleccionar un cliente.',
            'client_id.exists'      => 'El cliente seleccionado no es válido.',
            'guarantor_id.exists'   => 'El garante seleccionado no es válido.',
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
        //   CÁLCULO SERVER-SIDE
        // ============================
        $amount       = (float) $data['amount'];
        $termMonths   = (int) $data['term_months'];
        $interestRate = (float) $data['interest_rate'];

        if ($termMonths > 0) {
            $interestAmount = $amount * ($interestRate / 100);
            $monthly        = $amount + $interestAmount;
            $total          = $monthly * $termMonths;

            $data['monthly_payment'] = round($monthly, 2);
            $data['total_payable']   = round($total, 2);
        }

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
        // ❌ No permitir borrar préstamos finalizados
        if ($loan->status === 'finished') {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se puede eliminar un préstamo que ya está finalizado.',
            ], 409);
        }

        try {
            $loan->delete();

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
}
