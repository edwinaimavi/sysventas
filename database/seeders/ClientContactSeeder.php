<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientContact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         if (Client::count() === 0) {
            Client::factory(5)->create();
        }

          // Por cada cliente existente, crear entre 1 y 3 contactos
        Client::all()->each(function (Client $client) {
            $count = rand(1, 3);
            ClientContact::factory()->count($count)->create([
                'client_id' => $client->id,
            ]);

            // Asegurar que al menos uno sea primary
            if (! $client->clientContacts()->where('is_primary', true)->exists()) {
                $firstContact = $client->clientContacts()->first();
                if ($firstContact) {
                    $firstContact->update(['is_primary' => true]);
                }
            }
        });
    }
}
