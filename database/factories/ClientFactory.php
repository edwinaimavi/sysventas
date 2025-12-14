<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         // Definir tipo de documento aleatorio
        $documentType = $this->faker->randomElement(['DNI', 'CE', 'RUC']);
        $documentNumber = match ($documentType) {
            'DNI' => $this->faker->numerify('########'),
            'CE'  => $this->faker->numerify('#########'),
            'RUC' => $this->faker->numerify('############'),
        };

        $isCompany = $documentType === 'RUC';

        return [
            'branch_id' => Branch::inRandomOrder()->value('id') ?? Branch::factory(),
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),

            'document_type' => $documentType,
            'document_number' => $documentNumber,

            'full_name' => $isCompany
                ? $this->faker->company()
                : $this->faker->name(),

            'first_name' => $isCompany ? null : $this->faker->firstName(),
            'last_name' => $isCompany ? null : $this->faker->lastName(),

            'birth_date' => $isCompany ? null : $this->faker->date('Y-m-d', '-18 years'),
            'gender' => $isCompany ? null : $this->faker->randomElement(['M', 'F']),
            'marital_status' => $isCompany ? null : $this->faker->randomElement(['Soltero', 'Casado', 'Divorciado', 'Viudo']),
            'occupation' => $isCompany ? null : $this->faker->optional()->jobTitle(),

            'company_name' => $isCompany ? $this->faker->company() : null,
            'ruc' => $isCompany ? $documentNumber : null,

            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional()->numerify('9########'),
            'photo' => $this->faker->optional()->imageUrl(200, 200, 'people'),
            'status' => $this->faker->boolean(90),
            'credit_score' => $this->faker->optional()->randomFloat(2, 0, 100),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
