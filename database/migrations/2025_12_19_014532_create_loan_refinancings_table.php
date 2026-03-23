<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_refinances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // quién refinancia

            // Datos del refinanciamiento
            $table->date('refinance_date');                 // fecha en que se refinancia
            $table->date('new_due_date')->nullable();       // nueva fecha de vencimiento
            $table->integer('new_term_months')->nullable(); // nuevo plazo en meses

            // Base sobre la cual se calcula el nuevo interés
            $table->decimal('base_balance', 12, 2);         // saldo a refinanciar (ej: 600 o 300)

            // Interés configurado por admin
            $table->decimal('interest_rate', 5, 2)->default(0);    // ej 20.00
            $table->decimal('interest_amount', 12, 2)->default(0); // base_balance * rate
            $table->decimal('new_total_payable', 12, 2)->default(0); // base + interés (o total nuevo si deseas)

            // Snapshot (opcional pero útil para historial)
            $table->decimal('prev_total_payable', 12, 2)->nullable();  // total anterior del préstamo
            $table->decimal('prev_remaining_balance', 12, 2)->nullable(); // saldo anterior antes de refinanciar

            // Control
            $table->string('status', 20)->default('active'); // active|finished|canceled
            $table->text('notes')->nullable();

            $table->timestamps();

            // FK
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            // Index
            $table->index(['loan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_refinances');
    }
};
