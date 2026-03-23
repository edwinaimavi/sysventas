<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_loan_schedules_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();

            $table->unsignedInteger('installment_no'); // 1..N
            $table->date('due_date');

            // Saldo/capital al inicio del periodo
            $table->decimal('opening_balance', 12, 2)->default(0);

            // Componentes de la cuota
            $table->decimal('interest', 12, 2)->default(0);
            $table->decimal('amortization', 12, 2)->default(0);

            // Cuota fija
            $table->decimal('payment', 12, 2)->default(0);

            // Saldo final del periodo
            $table->decimal('closing_balance', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['loan_id', 'installment_no']);
            $table->index(['loan_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_schedules');
    }
};
