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
        Schema::create('reminders', function (Blueprint $table) {

            $table->id();

            // 🔹 Relaciones
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();      // destinatario
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('loan_id')->nullable();

            // 🔹 Contenido
            $table->string('title', 150);
            $table->text('message')->nullable();

            // 🔹 Clasificación
            $table->string('type', 50)->default('manual'); // manual | payment_due | payment_overdue | loan_finish ...
            $table->string('priority', 20)->default('normal'); // low | normal | high

            // 🔹 Fechas importantes
            $table->dateTime('remind_at')->nullable();     // cuándo se debe disparar
            $table->dateTime('expires_at')->nullable();    // opcional
            $table->date('created_for_date')->nullable();  // día conceptual

            // 🔹 Estado del recordatorio
            $table->string('status', 20)->default('pending');
            // pending | triggered | cancelled

            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->dateTime('sent_at')->nullable(); // cuando se envió la notificación

            // 🔹 Canal
            $table->string('channel', 30)->default('system'); // system | email | whatsapp | sms
            $table->string('channel_status', 30)->nullable(); // pending | sent | failed
            $table->text('channel_response')->nullable();

            // 🔹 Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // 🔹 Llaves foráneas opcionales (sin restricciones estrictas)
            $table->index('branch_id');
            $table->index('user_id');
            $table->index('client_id');
            $table->index('loan_id');
            $table->index('status');
            $table->index('type');
            $table->index('remind_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
