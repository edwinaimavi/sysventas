<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Vehicle::class;
    public function definition(): array
    {
        $type = $this->faker->randomElement(['auto', 'moto', 'camioneta', 'camión']);
        $brand = $this->faker->randomElement(['Toyota','Hyundai','Kia','Nissan','Honda','Chevrolet','Ford']);
        $model = $this->faker->word();
        $year = $this->faker->numberBetween(1995, date('Y'));
        // Generar placa con buena entropía para evitar colisiones en seeders grandes
        $plate = strtoupper($this->faker->bothify('??-####-??'));

        return [
            'client_id' => Client::inRandomOrder()->value('id') ?? Client::factory(),
            'guarantor_id' => null,
            'loan_id' => Loan::inRandomOrder()->value('id') ?? null,
            'collateral_id' => Collateral::inRandomOrder()->value('id') ?? null,

            'type' => $type,
            'brand' => $brand,
            'model' => $model,
            'year' => (string) $year,
            'plate_number' => $plate,
            'vin' => $this->faker->optional()->bothify(strtoupper('VIN###########')),
            'engine_number' => $this->faker->optional()->bothify('ENG-#####'),
            'color' => $this->faker->safeColorName(),
            'mileage' => $this->faker->numberBetween(0, 300000),

            'appraised_value' => $this->faker->randomFloat(2, 500, 35000),
            'condition' => $this->faker->randomElement(['excellent','good','fair','poor']),
            'description' => $this->faker->optional()->sentence(),

            'registration_doc' => null,
            'photo' => $this->faker->optional()->imageUrl(640, 480, 'transport'),
            'status' => $this->faker->randomElement(['active','pledged','released','repossessed']),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
