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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            // Relaciones opcionales
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null'); // dueño (cliente)
            $table->foreignId('guarantor_id')->nullable()->constrained('guarantors')->onDelete('set null'); // si es garante
            $table->foreignId('loan_id')->nullable()->constrained('loans')->onDelete('set null'); // asociado a un loan (si aplica)
            $table->foreignId('collateral_id')->nullable()->constrained('collaterals')->onDelete('set null'); // si está ligado como collateral

            // Datos del vehículo
            $table->string('type', 40)->nullable(); // auto, moto, camioneta...
            $table->string('brand', 80)->nullable();
            $table->string('model', 80)->nullable();
            $table->string('year', 6)->nullable();
            $table->string('plate_number', 20)->unique()->nullable();
            $table->string('vin', 50)->nullable(); // Vehicle Identification Number
            $table->string('engine_number', 80)->nullable();
            $table->string('color', 40)->nullable();
            $table->unsignedInteger('mileage')->nullable(); // kilometraje

            // Valor y estado
            $table->decimal('appraised_value', 12, 2)->nullable();
            $table->string('condition', 40)->nullable(); // excellent, good, fair, poor
            $table->text('description')->nullable();

            // Documentos / fotos
            $table->string('registration_doc', 255)->nullable();
            $table->string('photo', 255)->nullable();

            // Estado dentro del sistema
            $table->string('status', 30)->default('active'); // active, pledged, released, repossessed

            $table->timestamps();

            // Índices usados frecuentemente
            $table->index(['client_id']);
            $table->index(['loan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
