<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Guarantor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GuarantorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear garantes para clientes existentes
        if (Client::count() === 0) {
            Client::factory(5)->create();
        }

        // Para cada cliente crear 1-2 garantes ligados
        Client::all()->each(function (Client $client) {
            Guarantor::factory()->count(rand(1, 2))->create([
                'client_id' => $client->id,
                'is_external' => false,
            ]);
        });

        // Además generar algunos garantes externos sueltos
        Guarantor::factory()->count(10)->create([
            'is_external' => true,
        ]);
    }
}
