<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanPayment; // si existe
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $branchId = session('branch_id'); // ya lo usas

        // Si todavía no hay sucursal seleccionada, igual cargamos la vista (para que salga el modal)
        // y devolvemos data vacía.
        if (!$branchId) {
            return view('home', [
                'kpis' => null,
                'chartLoans' => [],
                'lastLoans' => collect(),
                'nextPayments' => collect(),
            ]);
        }

        // ===== KPIs =====
        $totalClients = Client::where('branch_id', $branchId)->count();

        $totalLoans = Loan::where('branch_id', $branchId)->count();

        $loansByStatus = Loan::select('status', DB::raw('COUNT(*) as total'))
            ->where('branch_id', $branchId)
            ->groupBy('status')
            ->pluck('total', 'status');

        // Montos (ajusta campos si tu tabla es diferente)
        $sumDisbursed = Loan::where('branch_id', $branchId)
            ->where('status', 'disbursed')
            ->sum('amount');

        // Si tienes tabla de pagos, podrías sumar lo pagado:
        $sumPaid = DB::table('loan_disbursements') // o loan_payments, etc.
            ->join('loans', 'loans.id', '=', 'loan_disbursements.loan_id')
            ->where('loans.branch_id', $branchId)
            ->where('loan_disbursements.status', 'completed')
            ->sum('loan_disbursements.amount');

        // ===== CHART: préstamos creados últimos 6 meses =====
        $start = Carbon::now()->subMonths(5)->startOfMonth();
        $months = collect(range(0, 5))->map(fn($i) => $start->copy()->addMonths($i));

        $counts = Loan::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
            DB::raw('COUNT(*) as total')
        )
            ->where('branch_id', $branchId)
            ->where('created_at', '>=', $start)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $chartLoans = $months->map(function ($m) use ($counts) {
            $ym = $m->format('Y-m');
            return [
                'label' => $m->translatedFormat('M Y'),
                'total' => (int) ($counts[$ym] ?? 0),
            ];
        });

        // ===== Últimos préstamos =====
        $lastLoans = Loan::with('client')
            ->where('branch_id', $branchId)
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // ===== Próximos pagos / vencidos (SI TIENES TABLA DE CUOTAS) =====
        // Si no tienes LoanPayment, comenta esta parte.
        // ===== PRÓXIMOS PRÉSTAMOS A VENCER =====
        $nextPayments = Loan::with('client')
            ->where('branch_id', $branchId)
            ->whereNotNull('due_date')
            ->whereIn('status', ['approved', 'disbursed'])
            ->orderBy('due_date', 'asc')
            ->limit(8)
            ->get();


        $kpis = [
            'totalClients' => $totalClients,
            'totalLoans' => $totalLoans,
            'sumDisbursed' => $sumDisbursed,
            'sumPaid' => $sumPaid,
            'loansByStatus' => $loansByStatus,
        ];

        return view('home', compact('kpis', 'chartLoans', 'lastLoans', 'nextPayments'));
    }
}
