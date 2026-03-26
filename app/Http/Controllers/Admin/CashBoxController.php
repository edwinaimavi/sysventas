<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CashBox;
use App\Models\CashMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class CashBoxController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.cashbox.index')->only('index', 'list');
    }
    public function index()
    {
        return view('admin.cash-box.index');
    }

    public function list()
    {
        $branchId = session('branch_id'); // 👈 sucursal activa

        $query = CashBox::with(['branch', 'openedBy', 'closedBy']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $cashBoxes = $query->orderBy('id', 'desc')->get();

        return DataTables::of($cashBoxes)
            ->addIndexColumn()

            // ✅ Sucursal
            ->addColumn('branch', function ($cash) {
                return $cash->branch->name ?? '-';
            })

            // ✅ Fecha apertura
            ->editColumn('opened_at', function ($cash) {
                return optional($cash->opened_at)->format('d/m/Y H:i');
            })

            // ✅ Saldo inicial
            ->editColumn('opening_amount', function ($cash) {
                return 'S/ ' . number_format($cash->opening_amount, 2);
            })

            /*
        |--------------------------------------------------------------------------
        | COLUMNAS TEMPORALES (OPCIÓN A)
        | Luego se reemplazan por movimientos reales
        |--------------------------------------------------------------------------
        */

            ->addColumn('ingresos', function ($cash) {
                $income = $cash->movements()
                    ->where('type', 'in')
                    ->sum('amount');

                return 'S/ ' . number_format($income, 2);
            })

            ->addColumn('egresos', function ($cash) {
                $expense = $cash->movements()
                    ->where('type', 'out')
                    ->sum('amount');

                return 'S/ ' . number_format($expense, 2);
            })

            ->addColumn('saldo_final', function ($cash) {

                $income = $cash->movements()
                    ->where('type', 'in')
                    ->sum('amount');

                $expense = $cash->movements()
                    ->where('type', 'out')
                    ->sum('amount');

                $balance = $income - $expense;

                return 'S/ ' . number_format($balance, 2);
            })
            // ✅ Estado
            ->addColumn('status_badge', function ($cash) {

                if ($cash->status === 'open') {
                    return '<span class="badge bg-success px-3 py-2">
                            <i class="fas fa-lock-open me-1"></i> ABIERTA
                        </span>';
                }

                return '<span class="badge bg-secondary px-3 py-2">
                        <i class="fas fa-lock me-1"></i> CERRADA
                    </span>';
            })

            // ✅ Acciones
            ->addColumn('actions', function ($cash) {
                return view('admin.cash-box.partials.acciones', compact('cash'))->render();
            })

            ->rawColumns([
                'status_badge',
                'actions'
            ])
            ->make(true);
    }

    public function summary($id)
    {
        $cash = CashBox::findOrFail($id);

        // 🔥 TEMPORAL (luego vendrá de movimientos reales)
        $totalIncome = $cash->total_income ?? 0;
        $totalExpense = $cash->total_expense ?? 0;

        $expectedBalance =
            $cash->opening_amount +
            $totalIncome -
            $totalExpense;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cash->id,
                'opening_amount' => $cash->opening_amount,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'expected_balance' => $expectedBalance
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // 🔐 sucursal obligatoria desde sesión
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No hay una sucursal seleccionada en la sesión.'
            ], 422);
        }

        // ✅ Validación
        $data = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'opened_at'      => 'required|date',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // ❌ Regla de negocio: no 2 cajas abiertas
            $exists = CashBox::where('branch_id', $branchId)
                ->where('status', 'open')
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una caja abierta en esta sucursal.'
                ], 422);
            }

            // 🧠 Datos que NO vienen del request
            $data['branch_id'] = $branchId;
            $data['status']    = 'open';
            $data['opened_by'] = Auth::id();

            $cash = CashBox::create($data);

            // ✅ MOVIMIENTO DE APERTURA (AQUÍ VA)
            CashMovement::create([
                'cash_box_id' => $cash->id,
                'branch_id'   => $branchId,
                'type'        => 'in',
                'concept'     => 'opening',
                'amount'      => $cash->opening_amount,
                'notes'       => $data['notes'] ?? null,
                'user_id'     => Auth::id(),

                // 🔥 NUEVO (CLAVE)
                'reference_table' => 'cash_boxes',
                'reference_id'    => $cash->id,
            ]);


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Caja aperturada correctamente.',
                'data'    => $cash
            ], 201);
        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error('Error abriendo caja: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al aperturar la caja.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /* =========================================================
 | REPOSICIÓN DE CAJA
 ========================================================= */
    public function replenish(Request $request)
    {
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sucursal seleccionada en la sesión.'
            ], 422);
        }

        $data = $request->validate([
            'cash_box_id' => 'required|exists:cash_boxes,id',
            'amount'      => 'required|numeric|min:0.01',
            'notes'       => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $cash = CashBox::where('id', $data['cash_box_id'])
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($cash->status !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => 'La caja está cerrada. No se puede reponer dinero.'
                ], 422);
            }

            CashMovement::create([
                'cash_box_id' => $cash->id,
                'branch_id'   => $branchId,
                'type'        => 'in',
                'concept'     => 'capital_replenishment',
                'amount'      => $data['amount'],
                'notes'       => $data['notes'] ?? null,
                'user_id'     => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reposición registrada correctamente.'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error('Error reposición caja: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la reposición.'
            ], 500);
        }
    }


    public function withdraw(Request $request)
    {
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sucursal seleccionada en la sesión.'
            ], 422);
        }

        $data = $request->validate([
            'cash_box_id' => 'required|exists:cash_boxes,id',
            'amount'      => 'required|numeric|min:0.01',
            'notes'       => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // 🔒 Bloqueo para evitar concurrencia
            $cash = CashBox::where('id', $data['cash_box_id'])
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->firstOrFail();

            // ❌ Validar que esté abierta
            if ($cash->status !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => 'La caja está cerrada. No se puede retirar dinero.'
                ], 422);
            }

            // 💰 Calcular saldo actual
            $totalIncome = $cash->movements()
                ->where('type', 'in')
                ->sum('amount');

            $totalExpense = $cash->movements()
                ->where('type', 'out')
                ->sum('amount');

            $currentBalance = $totalIncome - $totalExpense;

            // 🚫 VALIDACIÓN CLAVE
            if ($data['amount'] > $currentBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay suficiente dinero en caja. Saldo actual: S/ ' . number_format($currentBalance, 2)
                ], 422);
            }

            // 🧾 Registrar egreso
            CashMovement::create([
                'cash_box_id' => $cash->id,
                'branch_id'   => $branchId,
                'type'        => 'out',
                'concept'     => 'expense', // 👈 puedes cambiar luego a categorías
                'amount'      => $data['amount'],
                'notes'       => $data['notes'] ?? null,
                'user_id'     => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retiro registrado correctamente.'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error('Error retiro caja: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el retiro.'
            ], 500);
        }
    }


    public function movements($id)
    {
        $cash = CashBox::with(['movements.user'])->findOrFail($id);

        $movements = $cash->movements()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // 🔢 Totales
        $income = $movements->where('type', 'in')->sum('amount');
        $expense = $movements->where('type', 'out')->sum('amount');
        $balance = $income - $expense;

        return response()->json([
            'success' => true,
            'data' => [
                'opening' => $cash->opening_amount,
                'income'  => $income,
                'expense' => $expense,
                'balance' => $balance,
                'movements' => $movements
            ]
        ]);
    }


    public function pdf($id)
    {
        $cash = CashBox::with(['movements.user', 'branch'])->findOrFail($id);

        $movements = $cash->movements()->with('user')->get();

        $income = $movements->where('type', 'in')->sum('amount');
        $expense = $movements->where('type', 'out')->sum('amount');
        $balance = $income - $expense;

        $pdf = \PDF::loadView('admin.cash-box.pdf.detail', compact(
            'cash',
            'movements',
            'income',
            'expense',
            'balance'
        ));

        return $pdf->download('detalle_caja.pdf');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
