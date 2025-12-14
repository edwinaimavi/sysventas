<?php

namespace Database\Seeders;

use App\Models\Collateral;
use App\Models\Loan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollateralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Loan::count() === 0) {
            Loan::factory(5)->create();
        }

        Loan::all()->each(function (Loan $loan) {
            $count = rand(1, 2); // cada préstamo 1–2 garantías
            Collateral::factory()->count($count)->create([
                'loan_id' => $loan->id
            ]);
        });
    }
}
