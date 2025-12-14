<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guarantor>
 */
class GuarantorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       // decidir si es cliente interno o externo
        $isExternal = $this->faker->boolean(70); // 70% garantes externos por defecto

        // tipo y número de documento
        $docType = $this->faker->randomElement(['DNI', 'CE', 'RUC']);
        $docNumber = match ($docType) {
            'DNI' => $this->faker->numerify('########'),
            'CE'  => $this->faker->numerify('#########'),
            'RUC' => $this->faker->numerify('############'),
        };

        $isCompany = $docType === 'RUC';

        return [
            'client_id' => $isExternal ? null : Client::inRandomOrder()->value('id') ?? Client::factory(),
            'is_external' => $isExternal,

            'document_type' => $docType,
            'document_number' => $docNumber,

            'full_name' => $isCompany ? $this->faker->company() : $this->faker->name(),
            'first_name' => $isCompany ? null : $this->faker->firstName(),
            'last_name' => $isCompany ? null : $this->faker->lastName(),

            'company_name' => $isCompany ? $this->faker->company() : null,
            'ruc' => $isCompany ? $docNumber : null,

            'phone' => $this->faker->optional()->numerify('9########'),
            'alt_phone' => $this->faker->optional(0.4)->numerify('9########'),
            'email' => $this->faker->optional()->safeEmail(),
            'address' => $this->faker->optional()->streetAddress(),

            'relationship' => $this->faker->optional()->randomElement(['Padre','Madre','Hermano','Amigo','Vecino','Jefe','Cónyuge']),
            'occupation' => $this->faker->optional()->jobTitle(),
            'photo' => $this->faker->optional()->imageUrl(200,200,'people'),

            'status' => $this->faker->boolean(90),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
