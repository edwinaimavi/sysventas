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
        Schema::create('loan_increments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->decimal('old_amount', 12, 2);
            $table->decimal('increment_amount', 12, 2);
            $table->decimal('new_amount', 12, 2);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_increments');
    }
};
