<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('collaterals', function (Blueprint $table) {
            $table->id();

            // Relación con el loan
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');

            // Tipo principal de garantía
            $table->string('type', 50); 
            // Ej: 'vehicle', 'property', 'jewelry', 'electronics', 'others'

            // Descripción general
            $table->string('description', 255)->nullable();

            // Valor estimado
            $table->decimal('estimated_value', 12, 2)->nullable();

            // Información específica (JSON)
            $table->json('details')->nullable();
            /*
                Para no crear 30 columnas:
                - vehículo: placa, marca, modelo, año, color
                - inmueble: dirección, nro_partida, area
                - joya: material, peso
                - etc.
            */

            // Fotos o documentos asociados
            $table->string('photo', 255)->nullable();
            $table->string('document_file', 255)->nullable();

            // Estado
            $table->string('status', 30)->default('active'); 
            // active, released, confiscated, lost
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaterals');
    }
};
