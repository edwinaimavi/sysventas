<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanPayment; // ✅ asegúrate que exista
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LoansReportExport;
use App\Models\Branch;
use App\Models\CashMovement;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Client;
use App\Models\LoanDisbursement;
use App\Models\LoanSchedule;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;

class ReportController extends Controller
{
    public function index()
    {
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $clients  = Client::orderBy('full_name')->get(['id', 'full_name']); // ✅

        return view('admin.reports.index', compact('branches', 'clients'));
    }

    public function loans(Request $request)
    {
        $query = $this->getLoansQueryWithPaid($request)->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('client', fn($loan) => $loan->client->full_name ?? '—')
            ->editColumn('created_at', fn($loan) => optional($loan->created_at)->format('Y-m-d'))

            ->addColumn('paid_total', fn($loan) => 'S/ ' . number_format((float)($loan->paid_total ?? 0), 2))
            ->addColumn('capital_total', fn($loan) => 'S/ ' . number_format((float)($loan->capital_total ?? 0), 2))
            ->addColumn('interest_total', fn($loan) => 'S/ ' . number_format((float)($loan->interest_total ?? 0), 2))
            ->addColumn('installments_paid', fn($loan) => (int)($loan->installments_paid ?? 0))

            ->addColumn('remaining', function ($loan) {
                $remaining = max(0, ((float)$loan->total_payable - (float)($loan->paid_total ?? 0)));
                return 'S/ ' . number_format($remaining, 2);
            })

            ->editColumn('amount', fn($loan) => 'S/ ' . number_format((float)$loan->amount, 2))
            ->editColumn('total_payable', fn($loan) => 'S/ ' . number_format((float)$loan->total_payable, 2))

            ->editColumn('status', function ($loan) {
                $map = [
                    'pending'   => ['badge' => 'warning', 'label' => 'Pendiente'],
                    'approved'  => ['badge' => 'primary', 'label' => 'Aprobado'],
                    'disbursed' => ['badge' => 'success', 'label' => 'Desembolsado'],
                    'finished'  => ['badge' => 'dark', 'label' => 'Finalizado'],
                    'canceled'  => ['badge' => 'secondary', 'label' => 'Cancelado'],
                ];
                $info = $map[$loan->status] ?? ['badge' => 'secondary', 'label' => $loan->status];
                return "<span class='badge badge-{$info['badge']}'>{$info['label']}</span>";
            })
            ->rawColumns(['status'])
            ->make(true);
    }


    public function exportLoansPdf(Request $request)
    {
        // ✅ Traemos préstamos con total pagado en el rango
        $loans = $this->getLoansQueryWithPaid($request)
            ->orderBy('created_at', 'desc')
            ->get();

        // ✅ KPIs
        $totalPrestado  = (float) $loans->sum('amount');
        $totalAPagar    = (float) $loans->sum('total_payable');
        $totalRecuperado = (float) $loans->sum('paid_total');

        $gananciaEsperada = max(0, $totalAPagar - $totalPrestado);
        $pendienteTotal   = max(0, $totalAPagar - $totalRecuperado);

        $recoveryRate = $totalPrestado > 0
            ? round(($totalRecuperado / $totalPrestado) * 100, 2)
            : 0;

        $branchName = 'Todas';

        if ($request->branch_id) {
            $branchName = Branch::where('id', $request->branch_id)->value('name') ?? '—';
        }

        $clientName = 'Todos';
        if ($request->client_id) {
            $clientName = \App\Models\Client::where('id', $request->client_id)->value('full_name') ?? '—';
        }

        $filters = [
            'date_from'   => $request->date_from,
            'date_to'     => $request->date_to,
            'branch_name' => $branchName,
            'client_name' => $clientName, // ✅
        ];

        $summary = compact(
            'totalPrestado',
            'totalAPagar',
            'totalRecuperado',
            'gananciaEsperada',
            'pendienteTotal',
            'recoveryRate'
        );

        $pdf = Pdf::loadView('admin.reports.pdf.loans', compact('loans', 'summary', 'filters'))
            ->setPaper('A4', 'landscape');

        return $pdf->stream('reporte_consolidado_prestamos.pdf');
    }

    public function exportLoansExcel(Request $request)
    {
        return Excel::download(
            new LoansReportExport($request),
            'reporte_prestamos.xlsx'
        );
    }

    /**
     * ✅ Query con total pagado en el rango (por préstamo)
     */
    /**
     * ✅ Query con totales por préstamo EN EL RANGO:
     * - paid_total (monto)
     * - capital_total
     * - interest_total
     * - installments_paid (cuotas pagadas en el rango, usando paid_at de loan_schedules)
     */
    private function getLoansQueryWithPaid(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        return Loan::with(['client', 'branch'])

            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            /* ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id)) */

            /*  ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo)) */


            ->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($qq) => $qq->whereDate('created_at', '<=', $dateTo))

            ->addSelect([

                // ✅ Total recuperado (monto pagado) en el rango
                'paid_total' => LoanPayment::query()
                    ->selectRaw('COALESCE(SUM(loan_payments.amount),0)')
                    ->whereColumn('loan_payments.loan_id', 'loans.id')
                    ->where('loan_payments.status', 'completed')
                    ->when($dateFrom, fn($qq) => $qq->whereDate('loan_payments.payment_date', '>=', $dateFrom))
                    ->when($dateTo,   fn($qq) => $qq->whereDate('loan_payments.payment_date', '<=', $dateTo)),
                // ✅ Capital pagado en el rango
                'capital_total' => LoanPayment::query()
                    ->selectRaw('COALESCE(SUM(loan_payments.capital),0)')
                    ->whereColumn('loan_payments.loan_id', 'loans.id')
                    ->where('loan_payments.status', 'completed')
                    ->when($dateFrom, fn($qq) => $qq->whereDate('loan_payments.payment_date', '>=', $dateFrom))
                    ->when($dateTo,   fn($qq) => $qq->whereDate('loan_payments.payment_date', '<=', $dateTo)),


                // ✅ Interés pagado en el rango
                'interest_total' => LoanPayment::query()
                    ->selectRaw('COALESCE(SUM(loan_payments.interest),0)')
                    ->whereColumn('loan_payments.loan_id', 'loans.id')
                    ->where('loan_payments.status', 'completed')
                    ->when($dateFrom, fn($qq) => $qq->whereDate('loan_payments.payment_date', '>=', $dateFrom))
                    ->when($dateTo,   fn($qq) => $qq->whereDate('loan_payments.payment_date', '<=', $dateTo)),


                // ✅ # cuotas pagadas en el rango (cuotas que se marcaron PAID con paid_at dentro del rango)
                'installments_paid' => \App\Models\LoanSchedule::query()
                    ->selectRaw('COALESCE(COUNT(*),0)')
                    ->whereColumn('loan_id', 'loans.id')
                    ->where('status', 'paid')
                    ->when($dateFrom, fn($qq) => $qq->whereDate('paid_at', '>=', $dateFrom))
                    ->when($dateTo,   fn($qq) => $qq->whereDate('paid_at', '<=', $dateTo)),
            ]);
    }



    public function payments(Request $request)
    {
        $query = LoanPayment::with(['loan.client', 'branch', 'user'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->client_id, function ($q) use ($request) { // ✅
                $q->whereHas('loan', fn($qq) => $qq->where('client_id', $request->client_id));
            })
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
            ->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('loan_code', fn($p) => optional($p->loan)->loan_code ?? '—')
            ->addColumn('client_name', fn($p) => optional(optional($p->loan)->client)->full_name ?? '—')
            ->addColumn('method', fn($p) => $p->method ? ucfirst($p->method) : '—')
            ->editColumn('amount', fn($p) => 'S/ ' . number_format($p->amount, 2))
            ->editColumn('payment_date', fn($p) => $p->payment_date ? $p->payment_date->format('Y-m-d') : '—')
            ->make(true);
    }

    public function recovery(Request $request)
    {
        $branchId = $request->branch_id;

        $loansQ = Loan::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id)) // ✅
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $totalDisbursed = (float) $loansQ->sum('amount');

        $paidQ = LoanPayment::query()
            ->where('status', 'completed')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->client_id, function ($q) use ($request) { // ✅
                $q->whereHas('loan', fn($qq) => $qq->where('client_id', $request->client_id));
            })
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to));

        $totalPaid = (float) $paidQ->sum('amount');

        $rate = $totalDisbursed > 0 ? round(($totalPaid / $totalDisbursed) * 100, 2) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_disbursed' => round($totalDisbursed, 2),
                'total_paid' => round($totalPaid, 2),
                'recovery_rate' => $rate,
            ]
        ]);
    }


    public function operations(Request $request)
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        // =============================
        // APERTURA DE CAJA
        // =============================
        $apertura = CashMovement::query()
            ->where('concept', 'opening')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('amount');
        // =============================
        // REPOSICIÓN DE CAJA
        // =============================
        $reposicion = CashMovement::query()
            ->where('concept', 'capital_replenishment')
            ->where('type', 'in')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('amount');

        // =============================
        // PAGOS
        // =============================
        $payments = LoanPayment::query()
            ->where('status', 'completed')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('payment_date', '<=', $to))
            ->get();

        $montoCobrado   = $payments->sum('amount');
        $capital        = $payments->sum('capital');
        $interes        = $payments->sum('interest');
        $vueltoCliente  = $payments->sum('cash_change');

        // =============================
        // GASTOS ADICIONALES COBRADOS
        // =============================
        $gastosAdicionales = DB::table('loan_payment_expenses as e')
            ->join('loan_payments as p', 'p.id', '=', 'e.loan_payment_id')
            ->where('p.status', 'completed')
            ->when($request->branch_id, fn($q) => $q->where('p.branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('p.payment_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('p.payment_date', '<=', $to))
            ->sum('e.expense_amount');

        // =============================
        // SALIDAS (DESEMBOLSOS)
        // =============================
        $loans = Loan::query()
            ->whereIn('status', ['disbursed', 'finished'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('disbursement_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('disbursement_date', '<=', $to))
            ->get();

        $capitalRevolvente = $loans->where('term_months', 1)->sum('amount');
        $capitalCuotas     = $loans->where('term_months', '>', 1)->sum('amount');
        // =============================
        // OTRAS SALIDAS (RETIROS / GASTOS)
        // =============================
        $otrasSalidas = CashMovement::query()
            ->where('type', 'out')
            ->where('concept', 'expense') // 👈 clave
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('amount');

        // =============================
        // CÁLCULO REAL DE CAJA
        // =============================
        // =============================
        // CÁLCULO REAL DE CAJA (DESDE MOVIMIENTOS)
        // =============================
        $totalIngresos = CashMovement::query()
            ->where('type', 'in')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('amount');

        $totalSalidas = CashMovement::query()
            ->where('type', 'out')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('amount');

        $saldoCaja = $totalIngresos - $totalSalidas;


        return response()->json([
            // INGRESOS
            'monto_apertura'     => round($apertura, 2),
            'reposicion_caja'    => round($reposicion, 2),
            'monto_cobrado'      => round($montoCobrado, 2),
            'capital_recuperado' => round($capital, 2),
            'intereses_cobrados' => round($interes, 2),
            'gastos_adicionales' => round($gastosAdicionales, 2),

            // SALIDAS
            'vuelto_cliente'     => round($vueltoCliente, 2),
            'capital_revolvente' => round($capitalRevolvente, 2),
            'capital_cuotas'     => round($capitalCuotas, 2),
            'otras_salidas' => round($otrasSalidas, 2),

            // CAJA
            'saldo_caja' => round($saldoCaja, 2),
        ]);
    }




    public function commercial(Request $request)
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        $loans = Loan::query()
            ->whereIn('status', ['disbursed', 'finished']) // ✅
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($from, fn($q) => $q->whereDate('disbursement_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('disbursement_date', '<=', $to))
            ->get();

        $loanIds = $loans->pluck('id');

        /*
    |--------------------------------------------------------------------------
    | 1️⃣ CAPITAL REVOLVENTE (1 cuota)
    |--------------------------------------------------------------------------
    */
        $revLoans = $loans->where('term_months', 1);

        $revCapital = $revLoans->sum('amount');
        $revInterest = $revLoans->sum(fn($l) => $l->total_payable - $l->amount);

        /*
    |--------------------------------------------------------------------------
    | 2️⃣ CAPITAL EN CUOTAS (>1)
    |--------------------------------------------------------------------------
    */
        $cuoLoans = $loans->where('term_months', '>', 1);

        $cuoCapital = $cuoLoans->sum('amount');
        $cuoInterest = $cuoLoans->sum(fn($l) => $l->total_payable - $l->amount);

        /*
    |--------------------------------------------------------------------------
    | 3️⃣ CAPITAL VENCIDO (SOLO CUOTAS VENCIDAS)
    |--------------------------------------------------------------------------
    */
        $vencidoCapital = LoanSchedule::whereIn('loan_id', $loanIds)
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now())
            ->sum('amortization');

        $vencidoInterest = LoanSchedule::whereIn('loan_id', $loanIds)
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now())
            ->sum('interest');

        return response()->json([
            'revolvente' => [
                'capital'  => round($revCapital, 2),
                'interest' => round($revInterest, 2),
            ],
            'cuotas' => [
                'capital'  => round($cuoCapital, 2),
                'interest' => round($cuoInterest, 2),
            ],
            'vencido' => [
                'capital'  => round($vencidoCapital, 2),
                'interest' => round($vencidoInterest, 2),
            ],
        ]);
    }


    // ReportController.php
    private function getCommercialData(Request $request): array
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        $loans = Loan::query()
            ->with('client')
            ->where('status', 'disbursed')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->get();

        $loanIds = $loans->pluck('id');

        // 1️⃣ Revolvente
        $revLoans = $loans->where('term_months', 1);
        $revCapital = $revLoans->sum('amount');
        $revInterest = $revLoans->sum(fn($l) => $l->total_payable - $l->amount);

        // 2️⃣ Cuotas
        $cuoLoans = $loans->where('term_months', '>', 1);
        $cuoCapital = $cuoLoans->sum('amount');
        $cuoInterest = $cuoLoans->sum(fn($l) => $l->total_payable - $l->amount);

        // 3️⃣ Vencido
        $vencidoCapital = LoanSchedule::whereIn('loan_id', $loanIds)
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now())
            ->sum('capital');

        $vencidoInterest = LoanSchedule::whereIn('loan_id', $loanIds)
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now())
            ->sum('interest');

        return compact(
            'loans',
            'revCapital',
            'revInterest',
            'cuoCapital',
            'cuoInterest',
            'vencidoCapital',
            'vencidoInterest'
        );
    }



    public function exportCommercialPdf(Request $request)
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        $loans = Loan::with('client')
            ->whereIn('status', ['disbursed', 'finished'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->client_id, fn($q) => $q->where('client_id', $request->client_id))
            ->when($from, fn($q) => $q->whereDate('disbursement_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('disbursement_date', '<=', $to))
            ->get();

        $loanIds = $loans->pluck('id');

        // 🔹 Capital Revolvente
        $revLoans = $loans->where('term_months', 1);
        $revCapital = $revLoans->sum('amount');
        $revInterest = $revLoans->sum(fn($l) => $l->total_payable - $l->amount);

        // 🔹 Capital en Cuotas
        $cuoLoans = $loans->where('term_months', '>', 1);
        $cuoCapital = $cuoLoans->sum('amount');
        $cuoInterest = $cuoLoans->sum(fn($l) => $l->total_payable - $l->amount);

        // 🔹 Capital vencido
        $vencidoCapital = LoanSchedule::whereIn('loan_id', $loanIds)
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now())
            ->sum('amortization');

        $vencidoInterest = LoanSchedule::whereIn('loan_id', $loanIds)
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now())
            ->sum('interest');

        $filters = [
            'date_from' => $from,
            'date_to'   => $to,
        ];

        $pdf = Pdf::loadView(
            'admin.reports.pdf.commercial',
            compact(
                'loans',
                'revCapital',
                'revInterest',
                'cuoCapital',
                'cuoInterest',
                'vencidoCapital',
                'vencidoInterest',
                'filters'
            )
        )->setPaper('A4', 'portrait');

        return $pdf->stream('reporte_comercial.pdf');
    }




    public function operationsPdf(Request $request)
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        /*
    |--------------------------------------------------------------------------
    | PAGOS
    |--------------------------------------------------------------------------
    */

        $payments = LoanPayment::query()
            ->where('status', 'completed')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('payment_date', '<=', $to))
            ->get();

        $montoCobrado  = $payments->sum('amount');
        $capital       = $payments->sum('capital');
        $interes       = $payments->sum('interest');
        $vueltoCliente = $payments->sum('cash_change');

        /*
    |--------------------------------------------------------------------------
    | GASTOS ADICIONALES
    |--------------------------------------------------------------------------
    */

        $gastosAdicionales = DB::table('loan_payment_expenses as e')
            ->join('loan_payments as p', 'p.id', '=', 'e.loan_payment_id')
            ->where('p.status', 'completed')
            ->when($request->branch_id, fn($q) => $q->where('p.branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('p.payment_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('p.payment_date', '<=', $to))
            ->sum('e.expense_amount');

        /*
    |--------------------------------------------------------------------------
    | DESEMBOLSOS
    |--------------------------------------------------------------------------
    */

        $loans = Loan::query()
            ->whereIn('status', ['disbursed', 'finished'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('disbursement_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('disbursement_date', '<=', $to))
            ->get();

        $capitalRevolvente = $loans->where('term_months', 1)->sum('amount');
        $capitalCuotas     = $loans->where('term_months', '>', 1)->sum('amount');

        /*
    |--------------------------------------------------------------------------
    | 🔥 CAJA REAL (MISMO CÁLCULO QUE DASHBOARD)
    |--------------------------------------------------------------------------
    */

        $totalIngresos = CashMovement::query()
            ->where('type', 'in')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('amount');

        $totalSalidas = CashMovement::query()
            ->where('type', 'out')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('amount');

        $saldoCaja = $totalIngresos - $totalSalidas;

        /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

        $pdf = Pdf::loadView('admin.reports.pdf.operations', [
            'filters'            => $request->all(),
            'montoCobrado'       => $montoCobrado,
            'capitalRecuperado'  => $capital,
            'interesesCobrados'  => $interes,
            'gastosAdicionales'  => $gastosAdicionales,
            'vueltoCliente'      => $vueltoCliente,
            'capitalRevolvente'  => $capitalRevolvente,
            'capitalCuotas'      => $capitalCuotas,
            'totalIngresos'      => $totalIngresos,
            'totalSalidas'       => $totalSalidas,
            'saldoCaja'          => $saldoCaja,
        ]);

        return $pdf->stream('reporte-operaciones.pdf');
    }



    public function cashPdf(Request $request)
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        $payments = LoanPayment::query()
            ->where('status', 'completed')
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('payment_date', '<=', $to))
            ->get();

        $montoCobrado  = $payments->sum('amount');
        $vueltoCliente = $payments->sum('cash_change');

        $gastosAdicionales = DB::table('loan_payment_expenses as e')
            ->join('loan_payments as p', 'p.id', '=', 'e.loan_payment_id')
            ->where('p.status', 'completed')
            ->when($request->branch_id, fn($q) => $q->where('p.branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('p.payment_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('p.payment_date', '<=', $to))
            ->sum('e.expense_amount');


        $loans = Loan::query()
            ->whereIn('status', ['disbursed', 'finished'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('disbursement_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('disbursement_date', '<=', $to))
            ->get();

        $capitalRevolvente = $loans->where('term_months', 1)->sum('amount');
        $capitalCuotas     = $loans->where('term_months', '>', 1)->sum('amount');

        $totalIngresos = $montoCobrado + $gastosAdicionales - $vueltoCliente;
        $totalSalidas  = $capitalRevolvente + $capitalCuotas;
        $saldoCaja     = $totalIngresos - $totalSalidas;

        $pdf = Pdf::loadView('admin.reports.pdf.cash', [
            'filters'            => $request->all(),
            'montoCobrado'       => $montoCobrado,
            'gastosAdicionales'  => $gastosAdicionales,
            'vueltoCliente'      => $vueltoCliente,
            'capitalRevolvente'  => $capitalRevolvente,
            'capitalCuotas'      => $capitalCuotas,
            'totalIngresos'      => $totalIngresos,
            'totalSalidas'       => $totalSalidas,
            'saldoCaja'          => $saldoCaja,
        ])->setPaper('A4');

        return $pdf->stream('cuadre_caja.pdf');
    }



    public function details(Request $request)
    {
        $type = $request->detail_type;

        $from     = $request->date_from;
        $to       = $request->date_to;
        $branchId = $request->branch_id;
        $clientId = $request->client_id;

        $rows = collect();

        /* =====================================================
     | INGRESOS → PAGOS
     ===================================================== */
        if ($type === 'ingresos') {

            $rows = LoanPayment::with('loan.client')
                ->where('status', 'completed')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when(
                    $clientId,
                    fn($q) =>
                    $q->whereHas('loan', fn($qq) => $qq->where('client_id', $clientId))
                )
                ->when($from, fn($q) => $q->whereDate('payment_date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('payment_date', '<=', $to))
                ->get()
                ->values()
                ->map(function ($p, $i) {

                    $gastos = DB::table('loan_payment_expenses')
                        ->where('loan_payment_id', $p->id)
                        ->sum('expense_amount');

                    return [
                        'index'    => $i + 1,
                        'date'     => $p->payment_date,
                        'type'     => 'Ingreso',
                        'concept'  => 'Pago de préstamo',
                        'client'   => optional($p->loan->client)->full_name ?? '—',
                        'loan'     => optional($p->loan)->loan_code ?? '—',

                        'amount'   => $p->amount,
                        'capital'  => $p->capital,
                        'interest' => $p->interest,
                        'expenses' => $gastos, // 👈 NUEVO
                    ];
                });
        }



        /* =====================================================
     | SALIDAS → PRÉSTAMOS ENTREGADOS
     ===================================================== */
        if ($type === 'salidas') {

            $rows = Loan::with('client')
                ->whereIn('status', ['disbursed', 'finished'])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->when($from, fn($q) => $q->whereDate('disbursement_date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('disbursement_date', '<=', $to))
                ->get()
                ->values()
                ->map(function ($l, $i) {
                    return [
                        'index'   => $i + 1,
                        'date'    => $l->disbursement_date,
                        'type'    => 'Salida',
                        'concept' => 'Desembolso de préstamo',
                        'client'  => optional($l->client)->full_name ?? '—',
                        'loan'    => $l->loan_code ?? '—',
                        'amount'  => $l->amount,
                    ];
                });
        }

        /* =====================================================
     | APERTURA DE CAJA
     ===================================================== */
        if ($type === 'apertura') {

            $rows = CashMovement::query()
                ->where('concept', 'opening')
                ->where('type', 'in')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
                ->orderBy('created_at')
                ->get()
                ->values()
                ->map(function ($m, $i) {
                    return [
                        'index'   => $i + 1,
                        'date'    => $m->created_at,
                        'type'    => 'Ingreso',
                        'concept' => 'Apertura de caja',
                        'client'  => '—',
                        'loan'    => '—',
                        'amount'  => $m->amount,
                    ];
                });
        }
        /* =====================================================
| CUADRE DE CAJA → INGRESOS COMPLETOS
===================================================== */
        if ($type === 'cash_in') {

            $rows = collect();

            /*
    |--------------------------------------------------------------------------
    | 1️⃣ APERTURA
    |--------------------------------------------------------------------------
    */
            $aperturas = CashMovement::query()
                ->where('concept', 'opening')
                ->where('type', 'in')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
                ->get()
                ->map(function ($m) {
                    return [
                        'date'     => $m->created_at,
                        'type'     => 'Ingreso',
                        'concept'  => 'Apertura de caja',
                        'client'   => '—',
                        'loan'     => '—',
                        'amount'   => $m->amount,
                        'capital'  => 0,
                        'interest' => 0,
                        'expenses' => 0,
                    ];
                });

            /*
|--------------------------------------------------------------------------
| 2️⃣ REPOSICIONES
|--------------------------------------------------------------------------
*/
            $reposiciones = CashMovement::query()
                ->where('concept', 'capital_replenishment')
                ->where('type', 'in')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
                ->get()
                ->map(function ($m) {
                    return [
                        'date'     => $m->created_at,
                        'type'     => 'Ingreso',
                        'concept'  => 'Reposición de caja',
                        'client'   => '—',
                        'loan'     => '—',
                        'amount'   => $m->amount,
                        'capital'  => 0,
                        'interest' => 0,
                        'expenses' => 0,
                    ];
                });


            /*
    |--------------------------------------------------------------------------
    | 2️⃣ PAGOS
    |--------------------------------------------------------------------------
    */
            $payments = LoanPayment::with('loan.client')
                ->where('status', 'completed')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when(
                    $clientId,
                    fn($q) => $q->whereHas('loan', fn($qq) => $qq->where('client_id', $clientId))
                )
                ->when($from, fn($q) => $q->whereDate('payment_date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('payment_date', '<=', $to))
                ->get()
                ->map(function ($p) {

                    $gastos = DB::table('loan_payment_expenses')
                        ->where('loan_payment_id', $p->id)
                        ->sum('expense_amount');

                    return [
                        'date'     => $p->payment_date,
                        'type'     => 'Ingreso',
                        'concept'  => 'Pago de préstamo',
                        'client'   => optional($p->loan->client)->full_name ?? '—',
                        'loan'     => optional($p->loan)->loan_code ?? '—',
                        'amount'   => $p->amount,
                        'capital'  => $p->capital,
                        'interest' => $p->interest,
                        'expenses' => $gastos,
                    ];
                });

            /*
    |--------------------------------------------------------------------------
    | 3️⃣ UNIR TODO
    |--------------------------------------------------------------------------
    */
            $rows = $aperturas
                ->concat($reposiciones)
                ->concat($payments)
                ->values()
                ->map(function ($r, $i) {
                    $r['index'] = $i + 1;
                    return $r;
                });
        }


        /* =====================================================
     | SALIDAS DE CAJA (EGRESOS REALES)
     ===================================================== */
        if ($type === 'cash_out') {

            $rows = CashMovement::query()
                ->where('type', 'out')
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
                ->orderBy('created_at')
                ->get()
                ->values()
                ->map(function ($m, $i) {

                    $client = '—';
                    $loan   = '—';

                    // 🔹 Desembolso de préstamo
                    if ($m->concept === 'loan_disbursement' && $m->reference_id) {

                        $disbursement = LoanDisbursement::with('loan.client')
                            ->find($m->reference_id);

                        if ($disbursement && $disbursement->loan) {
                            $loan   = $disbursement->loan->loan_code;
                            $client = optional($disbursement->loan->client)->full_name ?? '—';
                        }
                    }

                    // 🔹 Vuelto / pagos (capital, interés, etc.)
                    if (in_array($m->concept, ['capital', 'interest', 'cash_change']) && $m->reference_id) {

                        $payment = LoanPayment::with('loan.client')
                            ->find($m->reference_id);

                        if ($payment && $payment->loan) {
                            $loan   = $payment->loan->loan_code;
                            $client = optional($payment->loan->client)->full_name ?? '—';
                        }
                    }

                    return [
                        'index'   => $i + 1,
                        'date'    => $m->created_at,
                        'type'    => 'Salida',
                        'concepto' => $this->translateConcept($m->concept),
                        'client'  => $client,
                        'loan'    => $loan,
                        'amount'  => $m->amount,
                    ];
                });
        }

        return response()->json([
            'detail' => $rows
        ]);
    }

    public function cashBook(Request $request)
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        $movements = CashMovement::query()
            ->with(['user'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderBy('created_at')
            ->get();

        $saldo = 0;

        $rows = $movements->map(function ($m) use (&$saldo) {

            $ingreso = $m->type == 'in' ? $m->amount : 0;
            $salida  = $m->type == 'out' ? $m->amount : 0;

            $saldo = $saldo + $ingreso - $salida;

            // 🔥 ESTA LÍNEA FALTABA
            $ctx = $this->getMovementContext($m);

            return [
                'fecha' => $m->created_at->format('Y-m-d H:i'),
                'concepto' => $this->translateConcept($m->concept),
                'notas' => $m->notes,

                'cliente' => $ctx['client'] ?? '-',
                'prestamo' => $ctx['loan_code'] ?? '-',

                'ingreso' => $ingreso,
                'salida' => $salida,
                'saldo' => $saldo
            ];
        });
        return response()->json([
            'data' => $rows
        ]);
    }

    private function translateConcept($concept)
    {
        $map = [
            'expense'              => 'Retiro de Caja',
            'loan_payment_expense' => 'Otros Ingresos',
            'capital'              => 'Pago de préstamo',
            'loan_disbursement'    => 'Desembolso de Préstamo',
            'loan_increment'       => 'Incremento de Préstamo',
            'opening'              => 'Apertura de Caja',
            'capital_replenishment'=> 'Reposición de Caja'
        ];

        return $map[$concept] ?? ucfirst(str_replace('_', ' ', $concept));
    }

    private function getMovementContext($m)
    {
        $client = null;
        $loanCode = null;

        switch ($m->reference_table) {

            case 'loan_payments':
                $payment = \App\Models\LoanPayment::with('loan.client')
                    ->find($m->reference_id);

                if ($payment && $payment->loan) {
                    $client = $payment->loan->client->full_name ?? null;
                    $loanCode = $payment->loan->loan_code ?? null;
                }
                break;

            case 'loan_payment_expenses':
                $expense = \App\Models\LoanPaymentExpense::with('payment.loan.client')
                    ->find($m->reference_id);

                if ($expense && $expense->payment && $expense->payment->loan) {
                    $client = $expense->payment->loan->client->full_name ?? null;
                    $loanCode = $expense->payment->loan->loan_code ?? null;
                }
                break;

            case 'loan_disbursements':
                $disbursement = \App\Models\LoanDisbursement::with('loan.client')
                    ->find($m->reference_id);

                if ($disbursement && $disbursement->loan) {
                    $client = $disbursement->loan->client->full_name ?? null;
                    $loanCode = $disbursement->loan->loan_code ?? null;
                }
                break;
        }

        return [
            'client' => $client,
            'loan_code' => $loanCode
        ];
    }
}
