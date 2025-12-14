<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'tipo_persona' => $this->faker->randomElement(['Natural', 'Juridica']),
            'tipo_doc' => $this->faker->randomElement(['DNI', 'RUC', 'CE']),
            'nro_doc' => $this->faker->unique()->numerify('########'),
            'nombres' => $this->faker->name(),
            'apellidos' => $this->faker->lastName(),     
            'razon_social' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'direccion' => $this->faker->address(),
            'ubigeo' => $this->faker->numberBetween(100000, 999999),
            'departamento' => $this->faker->state(),
            'provincia' => $this->faker->city(),
            'distrito' => $this->faker->citySuffix(),
            'password' => bcrypt('password'), // Default password
            'email_verified_at' => now(),
            'user_id' => null, // Assuming no user is associated by default
            'status' => 1, // Active by default   
        ];
    }
}
