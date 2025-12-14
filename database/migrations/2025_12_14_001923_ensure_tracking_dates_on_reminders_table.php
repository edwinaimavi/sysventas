<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            if (!Schema::hasColumn('reminders', 'triggered_at')) {
                $table->timestamp('triggered_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('reminders', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('triggered_at');
            }
            if (!Schema::hasColumn('reminders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('reminders', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('cancelled_at');
            }
        });
    }

    public function down(): void
    {
        // NO lo bajamos para evitar romper prod (opcional)
        // Si quieres, puedes dropear condicionalmente igual que arriba.
    }
};
