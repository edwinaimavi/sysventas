<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Guarantor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Pest\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         $client = Client::inRandomOrder()->first() ?? Client::factory()->create();
    $guarantor = Guarantor::inRandomOrder()->first();

    $amount = $this->faker->randomFloat(2, 500, 50000);
    $term = $this->faker->randomElement([6, 12, 18, 24, 36]);
    $rate = $this->faker->randomFloat(2, 10, 35);

    // Cálculos simples
    $monthlyRate = $rate / 100 / 12;
    $monthlyPayment = $amount * $monthlyRate / (1 - pow(1 + $monthlyRate, -$term));
    $totalPayable = $monthlyPayment * $term;

    return [
        'client_id' => $client->id,
        'guarantor_id' => $guarantor->id ?? null,
        'branch_id' => $client->branch_id,
        'user_id' => $client->user_id,

        'loan_code' => 'LN-' . strtoupper(Str::random(8)),

        'amount' => $amount,
        'term_months' => $term,
        'interest_rate' => $rate,
        'monthly_payment' => round($monthlyPayment, 2),
        'total_payable' => round($totalPayable, 2),
        'disbursement_date' => $this->faker->optional()->date(),

        'status' => $this->faker->randomElement(['pending','approved','rejected','disbursed','canceled']),
        'notes' => $this->faker->optional()->sentence(),

        'created_at' => now(),
        'updated_at' => now(),
    ];
    }
}
