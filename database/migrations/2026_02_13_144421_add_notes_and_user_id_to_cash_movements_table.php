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
        Schema::table('cash_movements', function (Blueprint $table) {

            // Observaciones del movimiento
            $table->text('notes')->nullable()->after('amount');

            // Usuario que realizó el movimiento
            $table->unsignedBigInteger('user_id')->nullable()->after('notes');

            // Relación con tabla users
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table) {

            $table->dropForeign(['user_id']);
            $table->dropColumn(['notes', 'user_id']);
        });
    }
};
