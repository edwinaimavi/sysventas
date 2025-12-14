<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Pest\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanPayment>
 */
class LoanPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = LoanPayment::class;
    public function definition(): array
    {
        $loan = Loan::inRandomOrder()->first() ?? Loan::factory()->create();

        $capital = $this->faker->randomFloat(2, 10, $loan->amount * 0.40);
        $interest = $this->faker->randomFloat(2, 5, 50);
        $late_fee = $this->faker->optional(0.3)->randomFloat(2, 1, 20);
        $amount = $capital + $interest + ($late_fee ?? 0);

        // Calcular saldo restante (simple)
        $remaining = max(0, $loan->amount - $amount);

        $method = $this->faker->randomElement(['cash', 'bank_transfer', 'yape', 'plin']);

        return [
            'loan_id' => $loan->id,
            'branch_id' => $loan->branch_id,
            'user_id' => $loan->user_id,

            'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
            'payment_date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),

            'amount' => $amount,
            'capital' => $capital,
            'interest' => $interest,
            'late_fee' => $late_fee,

            'method' => $method,
            'reference' => $this->faker->bothify('REF-####-###'),

            'receipt_number' => $this->faker->optional(0.6)->bothify('RCT-####'),
            'receipt_file' => null,

            'status' => $this->faker->randomElement(['completed', 'pending', 'reversed']),
            'remaining_balance' => $remaining,

            'notes' => $this->faker->optional()->sentence(),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
