<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //CREAMOS LAS SUCUSALES DE PRUEBA
        Branch::factory()->count(5)->create();

        // También incluimos algunas fijas (opcional)
      
        
    }
}
