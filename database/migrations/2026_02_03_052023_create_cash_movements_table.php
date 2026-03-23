<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_box_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['in', 'out']);
            $table->enum('concept', [
                'opening',
                'capital_replenishment',   
                'capital',
                'loan_disbursement',
                'loan_payment',
                'expense',
                'adjustment'
            ]);

            $table->decimal('amount', 12, 2);
            $table->string('reference_table')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
