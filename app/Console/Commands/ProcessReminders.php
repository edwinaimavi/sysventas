<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Reminder;

class ProcessReminders extends Command
{
    protected $signature = 'reminders:run {--dry : Simula sin guardar}';
    protected $description = 'Procesa recordatorios: dispara pendientes, marca enviados y cancela expirados.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $now = now();

        $this->info("🔁 reminders:run | dry=" . ($dry ? 'YES' : 'NO') . " | now={$now}");

        try {
            DB::beginTransaction();

            // 0) Cancelar recordatorios si el préstamo ya está finished/cancelled
            $endedLoanReminders = Reminder::query()
                ->whereIn('status', ['pending', 'triggered'])
                ->whereNotNull('loan_id')
                ->whereHas('loan', function ($q) {
                    $q->whereIn('status', ['finished', 'cancelled']);
                })
                ->get();

            foreach ($endedLoanReminders as $r) {
                if ($dry) continue;

                $r->status = 'cancelled';
                $r->cancelled_at = $now;
                $r->channel_status = 'failed';
                $r->save();
            }


            // 1) Cancelar expirados (pending y ya pasó expires_at)
            $expired = Reminder::query()
                ->where('status', 'pending')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', $now)
                ->get();

            foreach ($expired as $r) {
                if ($dry) continue;

                $r->status = 'cancelled';
                $r->cancelled_at = $now;
                $r->channel_status = $r->channel_status ?? 'failed';
                $r->save();
            }

            // 2) Disparar los que ya toca (pending, remind_at <= now, y no expirado)
            $toTrigger = Reminder::query()
                ->where('status', 'pending')
                ->where('remind_at', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', $now);
                })
                ->get();

            foreach ($toTrigger as $r) {
                // evita re-disparar
                if ($r->triggered_at) continue;

                if ($dry) continue;

                $r->status = 'triggered';
                $r->triggered_at = $now;

                // “enviar” (por ahora solo sistema)
                if (($r->channel ?? 'system') === 'system') {
                    $r->channel_status = 'sent';
                    $r->sent_at = $now;
                } else {
                    // si luego implementas email/whatsapp/sms, aquí disparas el job
                    $r->channel_status = $r->channel_status ?? 'pending';
                }

                $r->save();
            }

            DB::commit();

            $this->info("✅ Expirados cancelados: " . $expired->count());
            $this->info("✅ Disparados: " . $toTrigger->count());
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("reminders:run error: " . $e->getMessage());
            $this->error("❌ Error: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
