<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoanInstallmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Loan::count() === 0) {
            Loan::factory(5)->create();
        }

        // Cada préstamo tendrá entre 6 y 12 cuotas
        Loan::all()->each(function (Loan $loan) {
            $installments = rand(6, 12);

            for ($i = 1; $i <= $installments; $i++) {
                LoanInstallment::factory()->create([
                    'loan_id' => $loan->id,
                    'installment_number' => $i,
                    'due_date' => now()->addMonths($i)->format('Y-m-d'),
                ]);
            }
        });
    }
}
