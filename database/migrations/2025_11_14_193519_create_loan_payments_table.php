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
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('loan_id')->constrained()->onDelete('cascade'); // préstamo asociado
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null'); // sucursal
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // usuario que registró

            // Datos del pago
            $table->string('payment_code', 40)->unique(); // código interno del pago
            $table->date('payment_date');                 // fecha del pago
            $table->decimal('amount', 12, 2);             // monto pagado

            // Desglose (opcional)
            $table->decimal('capital', 12, 2)->nullable();  
            $table->decimal('interest', 12, 2)->nullable(); 
            $table->decimal('late_fee', 12, 2)->nullable(); // moras

            // Método de pago
            $table->string('method', 40)->nullable(); // cash, bank_transfer, yape, plin
            $table->string('reference', 120)->nullable(); // número operación / voucher

            // Comprobante
            $table->string('receipt_number', 120)->nullable();
            $table->string('receipt_file', 255)->nullable();

            // Estado del pago
            $table->string('status', 30)->default('completed'); // completed, pending, reversed

            // Saldo del préstamo luego del pago
            $table->decimal('remaining_balance', 12, 2)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['loan_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
