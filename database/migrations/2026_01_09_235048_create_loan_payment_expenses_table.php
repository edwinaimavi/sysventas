<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_payment_expenses', function (Blueprint $table) {
            $table->id();

            // Relación al pago (IMPORTANTE: tu tabla de pagos debe llamarse loan_payments)
            $table->unsignedBigInteger('loan_payment_id');

            // Para control interno
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            // Gasto adicional (NO afecta el préstamo)
            $table->decimal('expense_amount', 10, 2)->default(0);
            $table->string('expense_type', 50)->nullable(); // transport, atm_fee, other
            $table->string('expense_description', 255)->nullable();

            $table->timestamps();

            $table->foreign('loan_payment_id')
                ->references('id')
                ->on('loan_payments')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payment_expenses');
    }
};
