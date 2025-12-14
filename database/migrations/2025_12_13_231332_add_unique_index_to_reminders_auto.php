<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            // Si no tienes created_for_date, NO lo hagas aquí.
            // (en tu store ya existe 'created_for_date' en validate, así que asumo que sí lo tienes)
            $table->unique(['branch_id', 'loan_id', 'type', 'created_for_date'], 'reminders_auto_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropUnique('reminders_auto_unique');
        });
    }
};
