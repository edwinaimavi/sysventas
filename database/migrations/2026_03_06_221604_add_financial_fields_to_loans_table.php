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
        Schema::table('loans', function (Blueprint $table) {

            $table->decimal('current_balance', 10, 2)->default(0)->after('total_payable');
            $table->decimal('total_paid', 10, 2)->default(0)->after('current_balance');

            $table->boolean('is_refinanced')->default(false)->after('status');
            $table->integer('refinance_count')->default(0)->after('is_refinanced');

            $table->unsignedBigInteger('parent_loan_id')->nullable()->after('refinance_count');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {

            $table->dropColumn([
                'current_balance',
                'total_paid',
                'is_refinanced',
                'refinance_count',
                'parent_loan_id'
            ]);

        });
    }
};