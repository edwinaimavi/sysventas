<?php

// database/migrations/xxxx_add_payment_fields_to_loan_schedules.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_schedules', function (Blueprint $table) {
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment');
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending')->after('paid_amount');
            $table->date('paid_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('loan_schedules', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'status', 'paid_at']);
        });
    }
};
