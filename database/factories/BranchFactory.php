<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //GENERANDO FAKER CON DATOS MAS REALISTAS (ESPAÑOL / PERU)

            $faker = $this->faker;
            $faker-> locale('es_PE');

            //SIMULACION DE CODIGOS TIPO BR001, BR002 ....
            static $branchNumber = 1;
            $code = 'BR' . str_pad($branchNumber++, 3, '0', STR_PAD_LEFT);
        return [

            'name' => $faker->company . ' Sucursal',
            'code' => $code,
            'address' => $faker->address,
            'phone' => $faker->numerify('9########'),
            'email' => $faker->unique()->companyEmail,
            'manager_user_id' => User::inRandomOrder()->value('id') ?? null, // si existen usuarios
            'is_active' => $faker->boolean(90), // 90% activas
            'created_by' => User::inRandomOrder()->value('id') ?? null,
            'updated_by' => null,
            
            
        ];
    }
}
