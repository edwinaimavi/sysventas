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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            
        $table->foreignId('client_id')->constrained()->onDelete('cascade'); // Cliente solicitante
        $table->foreignId('guarantor_id')->nullable()->constrained()->onDelete('set null'); // Garante
        $table->foreignId('branch_id')->constrained()->onDelete('cascade'); // Sucursal donde se procesa
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que registra

        $table->string('loan_code', 30)->unique(); // Código interno del crédito

        $table->decimal('amount', 10, 2); // Monto solicitado
        $table->integer('term_months'); // Plazo en meses
        $table->decimal('interest_rate', 5, 2); // Interés anual (%)
        $table->decimal('monthly_payment', 10, 2)->nullable(); // Cuota generada
        $table->decimal('total_payable', 12, 2)->nullable(); // Total a pagar
        $table->date('disbursement_date')->nullable(); // Fecha de desembolso

        $table->string('status', 20)->default('pending'); 
        // Estados: pending, approved, rejected, disbursed, canceled

        $table->text('notes')->nullable(); // Notas internas
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
