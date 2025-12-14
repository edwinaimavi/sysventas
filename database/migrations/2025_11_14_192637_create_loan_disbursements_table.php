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
        Schema::create('loan_disbursements', function (Blueprint $table) {
            $table->id();
               // Relaciones
            $table->foreignId('loan_id')->constrained()->onDelete('cascade'); // préstamo asociado
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null'); // sucursal donde se desembolsó
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // usuario que realizó el desembolso

            // Datos del desembolso
            $table->string('disbursement_code', 40)->unique()->nullable(); // código interno del desembolso
            $table->decimal('amount', 12, 2); // monto desembolsado
            $table->date('disbursement_date'); // fecha del desembolso

            // Método y referencias
            $table->string('method', 40)->nullable(); // Ej: 'efectivo','yape plin','tranferencia '
            $table->string('reference', 120)->nullable(); // número de cuenta / cheque / transacción

            // Documentos / comprobantes
            $table->string('receipt_number', 120)->nullable();
            $table->string('receipt_file', 255)->nullable(); // ruta de archivo si suben comprobante

            // Estado y saldos
            $table->string('status', 30)->default('completed'); // pending, completed, reversed
            $table->decimal('remaining_balance', 12, 2)->nullable(); // opcional: saldo pendiente después del desembolso

            $table->text('notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['loan_id', 'disbursement_date']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_disbursements');
    }
};
