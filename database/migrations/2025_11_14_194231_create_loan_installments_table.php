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
        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
             // Relaciones
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');

            // Datos de la cuota
            $table->integer('installment_number'); // número de cuota: 1, 2, 3, etc.
            $table->date('due_date');              // fecha de vencimiento
            $table->decimal('amount', 12, 2);      // monto total de la cuota

            // Desglose (opcional)
            $table->decimal('capital', 12, 2)->nullable();
            $table->decimal('interest', 12, 2)->nullable();
            $table->decimal('late_fee', 12, 2)->nullable();

            // Estado de la cuota
            $table->string('status', 30)->default('pending'); 
            // pending, paid, overdue, partially_paid

            // Pagos relacionados
            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['loan_id', 'installment_number']);
            $table->index(['loan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_installments');
    }
};
