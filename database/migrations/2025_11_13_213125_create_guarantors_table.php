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
        Schema::create('guarantors', function (Blueprint $table) {
            $table->id();

           // Si el garante está registrado como cliente (opcional)
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');

            // Datos básicos del garante (si es externo se rellenan aquí)
            $table->boolean('is_external')->default(true); // true = no es cliente del sistema
            $table->string('document_type', 20)->nullable(); // DNI, CE, RUC...
            $table->string('document_number', 30)->nullable()->index();
            $table->string('full_name', 150); // nombre completo o razón social
            $table->string('first_name', 80)->nullable();
            $table->string('last_name', 80)->nullable();

            // Si aplica empresa/RUC
            $table->string('company_name', 150)->nullable();
            $table->string('ruc', 15)->nullable();

            // Contacto
            $table->string('phone', 20)->nullable();
            $table->string('alt_phone', 20)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('address', 255)->nullable();

            // Información adicional
            $table->string('relationship', 80)->nullable(); // Relación con el cliente (ej. padre, amigo)
            $table->string('occupation', 100)->nullable();
            $table->string('photo', 255)->nullable();

            // Estado y auditoría
            $table->boolean('status')->default(true); // 1 = activo
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Índices simplificados
            $table->index(['document_type', 'document_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guarantors');
    }
};
