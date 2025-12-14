<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\LoanDisbursement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoanDisbursementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Si no hay loans, crea algunos
        if (Loan::count() === 0) {
            Loan::factory(5)->create();
        }

        // Para algunos loans crear 1 o más desembolsos
        Loan::inRandomOrder()->take(5)->get()->each(function (Loan $loan) {
            // Crear entre 1 y 2 desembolsos por loan
            $count = rand(1, 2);
            LoanDisbursement::factory()->count($count)->create([
                'loan_id' => $loan->id,
                'branch_id' => $loan->branch_id,
                'user_id' => $loan->user_id,
            ]);

            // Opcional: si quieres marcar loan como 'disbursed' cuando haya desembolso
            $loan->update(['status' => 'disbursed']);
        });
    }
}
