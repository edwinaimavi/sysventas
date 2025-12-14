<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientContact>
 */
class ClientContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['Domicilio', 'Trabajo', 'Referencia', 'Otro'];

        $contactType = $this->faker->randomElement($types);

        // Si es referencia, generamos contact_name y relationship
        $isReference = $contactType === 'Referencia';

        return [
            'client_id'   => Client::inRandomOrder()->value('id') ?? Client::factory(),
            'contact_type'=> $contactType,

            'address'     => $this->faker->optional(0.9)->streetAddress(),
            'district'    => $this->faker->optional()->city(),
            'province'    => $this->faker->optional()->city(),
            'department'  => $this->faker->optional()->state(),
            'reference'   => $this->faker->optional()->sentence(6),

            'phone'       => $this->faker->optional()->numerify('9########'),
            'alt_phone'   => $this->faker->optional(0.5)->numerify('9########'),
            'email'       => $this->faker->optional()->safeEmail(),

            'contact_name'=> $isReference ? $this->faker->name() : null,
            'relationship'=> $isReference ? $this->faker->randomElement(['Padre','Madre','Hermano','Amigo','Vecino','Jefe']) : null,

            'is_primary'  => $this->faker->boolean(20),

            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
