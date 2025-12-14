<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanDisbursement;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanDisbursement>
 */
class LoanDisbursementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = LoanDisbursement::class;
    public function definition(): array
    {
         $loan = Loan::inRandomOrder()->first() ?? Loan::factory()->create();

        // Monto: por defecto, un desembolso parcial o total
        $max = (float) $loan->amount;
        $portion = $this->faker->randomFloat(2, 0.25, 1); // entre 25% y 100%
        $amount = round($max * $portion, 2);

        // Remaining balance after this disbursement (simple cálculo)
        $remaining = max(0, round($max - $amount, 2));

        $method = $this->faker->randomElement(['cash', 'bank_transfer', 'check']);

        return [
            'loan_id' => $loan->id,
            'branch_id' => $loan->branch_id ?? null,
            'user_id' => $loan->user_id ?? null,

            'disbursement_code' => 'DSB-' . Str::upper(Str::random(8)),
            'amount' => $amount,
            'disbursement_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),

            'method' => $method,
            'reference' => $method === 'bank_transfer' ? $this->faker->iban() : $this->faker->bothify('REF-####-###'),
            'receipt_number' => $this->faker->optional(0.7)->bothify('RCPT-####'),

            'receipt_file' => null,
            'status' => $this->faker->randomElement(['completed', 'pending', 'reversed']),
            'remaining_balance' => $remaining,
            'notes' => $this->faker->optional()->sentence(),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
