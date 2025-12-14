<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoanPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Loan::count() === 0) {
            Loan::factory(5)->create();
        }

        Loan::inRandomOrder()->take(5)->get()->each(function (Loan $loan) {
            $count = rand(1, 3); // cada préstamo tendrá 1 a 3 pagos

            LoanPayment::factory()->count($count)->create([
                'loan_id' => $loan->id,
                'branch_id' => $loan->branch_id,
                'user_id' => $loan->user_id,
            ]);
        });
    }
}
