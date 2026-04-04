<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;


class AdvancedReportController extends Controller
{
    public function index()
    {

        return view('admin.reports.advanced');
    }

    public function branches()
    {
        $branches = DB::table('branches')->get();

        return response()->json($branches);
    }

    public function data(Request $request)
    {
        $from = $request->date_from;
        $to   = $request->date_to;
        $kpi = $request->kpi_filter;

        $query = DB::table('loans as l')
            ->leftJoin('loan_payments as p', 'p.loan_id', '=', 'l.id')
            ->leftJoin('loan_payment_expenses as e', 'e.loan_payment_id', '=', 'p.id')
            ->join('clients as c', 'c.id', '=', 'l.client_id')

            ->select(
                'l.id',
                'l.loan_code as code',
                'l.amount',
                'l.created_at as loan_date',
                'l.due_date',
                'c.full_name as client',

                DB::raw('COALESCE(SUM(p.amount),0) as total_paid'),
                DB::raw('COALESCE(SUM(p.capital),0) as total_capital'),
                DB::raw('COALESCE(SUM(p.interest),0) as total_interest'),

                DB::raw('(MAX(l.total_payable) - COALESCE(SUM(p.amount),0)) as balance'),
                DB::raw('COALESCE(SUM(e.expense_amount),0) as total_expenses'),
                DB::raw("
   CASE 
    WHEN l.due_date < NOW()
    AND (MAX(l.total_payable) - COALESCE(SUM(p.amount),0)) > 0
    THEN 1
    ELSE 0
END as has_overdue"),

            )

            ->groupBy(
                'l.id',
                'l.due_date',
                'l.loan_code',
                'l.amount',
                'l.total_payable', // 👈 AGREGA ESTO
                'l.created_at',
                'c.full_name'
            );


        // 🔍 FILTRO POR FECHA
        if ($from && $to) {
            $query->whereBetween('l.created_at', [$from, $to]);
        }
        if (!$kpi) {
            $query->whereIn('l.status', ['disbursed']); // ✅ SOLO DESEMBOLSADOS POR DEFECTO
        }
        // 🔥 FILTROS POR KPI
        if ($kpi == 'overdue') {
            $query->where('l.status', 'disbursed') // ✅ SOLO DESEMBOLSADOS
                ->havingRaw("
            l.due_date < NOW()
            AND (MAX(l.total_payable) - COALESCE(SUM(p.amount),0)) > 0
        ");
        }

        if ($kpi == 'payments') {
            $query->havingRaw('COALESCE(SUM(p.amount),0) > 0');
        }

        if ($kpi == 'loans') {
            $query->where('l.status', 'disbursed'); // ✅ SOLO DESEMBOLSADOS
        }

        if ($kpi == 'pending') {
            $query->where('l.status', 'disbursed') // ✅ SOLO DESEMBOLSADOS
                ->havingRaw('(MAX(l.total_payable) - COALESCE(SUM(p.amount),0)) > 0');
        }
        return datatables()->of($query)

            ->addIndexColumn()

            ->editColumn('loan_date', function ($row) {
                return date('Y-m-d', strtotime($row->loan_date));
            })
            ->editColumn('due_date', function ($row) {
                return date('Y-m-d', strtotime($row->due_date));
            })

            ->editColumn('amount', function ($row) {
                return 'S/ ' . number_format($row->amount, 2);
            })

            ->editColumn('total_paid', function ($row) {
                return 'S/ ' . number_format($row->total_paid, 2);
            })

            ->editColumn('total_capital', function ($row) {
                return 'S/ ' . number_format($row->total_capital, 2);
            })

            ->editColumn('total_interest', function ($row) {
                return 'S/ ' . number_format($row->total_interest, 2);
            })
            ->editColumn('total_expenses', function ($row) {
                return 'S/ ' . number_format($row->total_expenses, 2);
            })

            ->editColumn('balance', function ($row) {

                $balance = round($row->balance, 2);

                if (abs($balance) < 0.05) {
                    $balance = 0;
                }

                return 'S/ ' . number_format($balance, 2);
            })

            ->addColumn('status', function ($row) {

                if ($row->has_overdue) {
                    return '<span class="badge badge-danger">Vencido</span>';
                }

                if ($row->balance <= 0) {
                    return '<span class="badge badge-success">Finalizado</span>';
                }

                return '<span class="badge badge-warning">Pendiente</span>';
            })

            ->addColumn('actions', function ($row) {
                return '
        <button class="btn btn-sm btn-info btn-view-loan"
            data-id="' . $row->id . '">
            <i class="fas fa-eye"></i>
        </button>
    ';
            })

            ->rawColumns(['status', 'actions'])

            ->make(true);
    }



    public function show($id)
    {
        $loan = DB::table('loans as l')
            ->join('clients as c', 'c.id', '=', 'l.client_id')
            ->where('l.id', $id)
            ->select(
                'l.id',
                'l.loan_code',
                'l.amount',
                'l.total_payable',
                'l.created_at',
                'l.due_date',
                'c.full_name as client'
            )
            ->first();

        if (!$loan) {
            return response()->json(['error' => 'Préstamo no encontrado'], 404);
        }

        $schedules = DB::table('loan_schedules')
            ->where('loan_id', $id)
            ->orderBy('installment_no') // 👈 OJO: en tu tabla es installment_no
            ->get();

        $payments = DB::table('loan_payments')
            ->where('loan_id', $id)
            ->orderBy('payment_date')
            ->get();

        return response()->json([
            'loan' => $loan,
            'schedules' => $schedules,
            'payments' => $payments
        ]);
    }


    public function kpis(Request $request)
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        // 🔹 TOTAL COLOCADO
        $totalLoans = DB::table('loans')
            ->where('status', 'disbursed')
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            })
            ->sum('amount');

        // 🔹 TOTAL PAGADO
        $totalPaid = DB::table('loan_payments as p')
            ->join('loans as l', 'l.id', '=', 'p.loan_id')
            ->where('l.status', 'disbursed')
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('l.created_at', [$from, $to]);
            })
            ->sum('p.amount');

        // 🔹 TOTAL A COBRAR (🔥 AQUÍ ESTABA EL ERROR)
        $totalPayable = DB::table('loans')
            ->where('status', 'disbursed') // 🔥 FIX
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            })
            ->sum('total_payable');

        // 🔹 SALDO
        $totalPending = $totalPayable - $totalPaid;

        // 🔴 CAPITAL VENCIDO
        $overdue = DB::table('loan_schedules as ls')
            ->join('loans as l', 'l.id', '=', 'ls.loan_id')
            ->where('l.status', 'disbursed') // ✅ SOLO DESEMBOLSADOS
            ->whereDate('l.due_date', '<', now()) // ✅ SOLO VENCIDOS
            ->where('ls.closing_balance', '>', 0) // ✅ CUOTAS PENDIENTES
            ->sum('ls.closing_balance');
        return response()->json([
            'total_loans'   => $totalLoans,
            'total_paid'    => $totalPaid,
            'total_pending' => $totalPending,
            'total_overdue' => $overdue
        ]);
    }

    private function baseQuery($request)
    {
        $query = DB::table('loans as l')
            ->leftJoin('loan_payments as p', 'p.loan_id', '=', 'l.id')
            ->leftJoin('loan_payment_expenses as e', 'e.loan_payment_id', '=', 'p.id')
            ->join('clients as c', 'c.id', '=', 'l.client_id')
            ->select(
                'l.loan_code',
                'l.amount',
                'l.created_at',
                'l.due_date',
                'c.full_name',
                DB::raw('COALESCE(SUM(p.amount),0) as total_paid'),
                DB::raw('COALESCE(SUM(p.capital),0) as total_capital'),
                DB::raw('COALESCE(SUM(p.interest),0) as total_interest'),
                DB::raw('COALESCE(SUM(e.expense_amount),0) as total_expenses'),
                DB::raw('(MAX(l.total_payable) - COALESCE(SUM(p.amount),0)) as balance')
            )
            ->groupBy(
                'l.id',
                'l.loan_code',
                'l.amount',
                'l.total_payable',
                'l.created_at',
                'l.due_date',
                'c.full_name'
            );

        // filtros
        if ($request->date_from && $request->date_to) {
            $query->whereBetween('l.created_at', [$request->date_from, $request->date_to]);
        }

        if ($request->branch_id) {
            $query->where('l.branch_id', $request->branch_id);
        }

        return $query->get();
    }



    public function exportExcel(Request $request)
    {
        $data = $this->baseQuery($request);

        return Excel::download(new \App\Exports\AdvancedReportExport($data), 'reporte.xlsx');
    }



    public function exportPdf(Request $request)
    {
        $data = $this->baseQuery($request);

        $pdf = FacadePdf::loadView('admin.reports.pdf.advanced', compact('data'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('reporte.pdf');
    }


    public function payments(Request $request)
    {
        $query = DB::table('loan_payments as p')
            ->join('loans as l', 'l.id', '=', 'p.loan_id')
            ->join('clients as c', 'c.id', '=', 'l.client_id')

            // 🔥 SOLO MÉTODOS VÁLIDOS
            ->whereIn('p.method', ['cash', 'yape', 'plin', 'bank_transfer'])

            ->select(
                'p.id',
                'p.payment_code',
                'l.loan_code',
                'c.full_name as client_name',
                'p.payment_date',
                'p.amount',
                'p.method'
            );

        // 🔹 FILTRO POR FECHA
        if ($request->date_from && $request->date_to) {
            $query->whereBetween('p.payment_date', [$request->date_from, $request->date_to]);
        }

        // 🔹 FILTRO POR SUCURSAL
        if ($request->branch_id) {
            $query->where('p.branch_id', $request->branch_id);
        }

        // 🔥 FILTRO POR MÉTODO (EL SELECT QUE VAMOS A CREAR)
        if ($request->payment_method) {
            $query->where('p.method', $request->payment_method);
        }

        return datatables()->of($query)
            ->addIndexColumn()

            ->editColumn('amount', function ($row) {
                return 'S/ ' . number_format($row->amount, 2);
            })

            ->editColumn('method', function ($row) {
                return ucfirst($row->method);
            })

            ->make(true);
    }
}
