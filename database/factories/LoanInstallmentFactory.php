<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanInstallment>
 */
class LoanInstallmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = LoanInstallment::class;
    public function definition(): array
    {
       $loan = Loan::inRandomOrder()->first() ?? Loan::factory()->create();

        $capital = $this->faker->randomFloat(2, 20, max(20, $loan->amount * 0.1));
        $interest = $this->faker->randomFloat(2, 5, 40);
        $late_fee = $this->faker->optional(0.2)->randomFloat(2, 1, 20);

        return [
            'loan_id' => $loan->id,
            // NO usar unique() — puede saturar cuando se generan muchas filas
            'installment_number' => $this->faker->numberBetween(1, 36),
            'due_date' => $this->faker->dateTimeBetween('-1 months', '+3 months')->format('Y-m-d'),

            'amount' => $capital + $interest + ($late_fee ?? 0),
            'capital' => $capital,
            'interest' => $interest,
            'late_fee' => $late_fee,

            'status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
            'paid_amount' => $this->faker->randomFloat(2, 0, $capital),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
