<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Si no hay clientes, crear algunos
        if (Client::count() === 0) {
            Client::factory(5)->create();
        }

        // Crear 1-2 vehículos por cliente
        Client::all()->each(function (Client $client) {
            $count = rand(1, 2);
            Vehicle::factory()->count($count)->create([
                'client_id' => $client->id,
            ]);
        });

        // Crear algunos vehículos sueltos (sin cliente) como inventario
        Vehicle::factory()->count(5)->create([
            'client_id' => null,
        ]);
    }
}
