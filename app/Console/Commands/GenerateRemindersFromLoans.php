<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Loan;
use App\Models\Reminder;
use Carbon\Carbon;

class GenerateRemindersFromLoans extends Command
{
    protected $signature = 'reminders:generate-from-loans
        {--branch= : Solo para una sucursal}
        {--dry : Simula, no guarda}
        {--due_before_days=2 : Días antes de due_date para payment_due}
        {--overdue_after_days=1 : Días después de due_date para payment_overdue}
        {--finish_before_days=3 : Días antes del fin para loan_finish}
    ';

    protected $description = 'Genera recordatorios automáticos desde loans (due/overdue/finish) evitando duplicados.';

    public function handle(): int
    {
        $dry = (bool)$this->option('dry');
        $branch = $this->option('branch');

        $dueBeforeDays     = (int)$this->option('due_before_days');
        $overdueAfterDays  = (int)$this->option('overdue_after_days');
        $finishBeforeDays  = (int)$this->option('finish_before_days');

        // ✅ Time window: catch-up + horizon
        $horizonDays = 30;   // genera recordatorios futuros
        $catchUpDays = 7;    // si el scheduler no corrió, igual los crea

        $now = now();
        $today = $now->copy()->startOfDay();

        $from = $today->copy()->subDays($catchUpDays);
        $to   = $today->copy()->addDays($horizonDays)->endOfDay();

        $this->info("🔁 reminders:generate-from-loans | dry=" . ($dry ? 'YES' : 'NO'));
        $this->info("   now={$now}");
        $this->info("   window: {$from->toDateTimeString()} -> {$to->toDateTimeString()}");
        $this->info("   due_before_days={$dueBeforeDays} | overdue_after_days={$overdueAfterDays} | finish_before_days={$finishBeforeDays}");

        try {
            DB::beginTransaction();

            $loans = Loan::query()
                ->when($branch, fn($q) => $q->where('branch_id', $branch))
                // ✅ Solo préstamos activos
                ->whereIn('status', ['approved', 'disbursed'])
                ->get();

            $created = 0;
            $skipped = 0;

            foreach ($loans as $loan) {

                // 1) payment_due (antes de due_date)
                if (!empty($loan->due_date)) {
                    $remindAt = Carbon::parse($loan->due_date)->subDays($dueBeforeDays)->setTime(9, 0, 0);

                    if ($remindAt->between($from, $to)) {
                        if ($this->createReminderIfNotExists($loan, 'payment_due', $remindAt, $dry)) $created++;
                        else $skipped++;
                    }

                    // 2) payment_overdue (después de due_date)
                    $remindAtOver = Carbon::parse($loan->due_date)->addDays($overdueAfterDays)->setTime(9, 0, 0);

                    if ($remindAtOver->between($from, $to)) {
                        if ($this->createReminderIfNotExists($loan, 'payment_overdue', $remindAtOver, $dry)) $created++;
                        else $skipped++;
                    }
                }

                // 3) loan_finish (fin estimado desde disbursement_date + term_months)
                if (!empty($loan->disbursement_date) && !empty($loan->term_months)) {
                    $finishDate = Carbon::parse($loan->disbursement_date)->addMonths((int)$loan->term_months);
                    $remindAtFinish = $finishDate->copy()->subDays($finishBeforeDays)->setTime(9, 0, 0);

                    if ($remindAtFinish->between($from, $to)) {
                        if ($this->createReminderIfNotExists($loan, 'loan_finish', $remindAtFinish, $dry, $finishDate)) $created++;
                        else $skipped++;
                    }
                }
            }

            DB::commit();

            $this->info("✅ Creados: {$created}");
            $this->info("⏭️  Omitidos (duplicados): {$skipped}");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("reminders:generate-from-loans error: " . $e->getMessage());
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }


    private function createReminderIfNotExists($loan, string $type, Carbon $remindAt, bool $dry, ?Carbon $finishDate = null): bool
    {
        $exists = Reminder::query()
            ->where('branch_id', $loan->branch_id)
            ->where('loan_id', $loan->id)
            ->where('type', $type)
            ->where('remind_at', $remindAt->format('Y-m-d H:i:s'))
            ->exists();

        if ($exists) {
            return false;
        }

        $title = $this->buildTitle($loan, $type, $finishDate);
        $message = $this->buildMessage($loan, $type, $finishDate);

        if ($dry) return true;

        Reminder::create([
            'branch_id' => $loan->branch_id,
            'user_id'   => $loan->user_id,      // dueño/creador del préstamo (puedes cambiarlo)
            'client_id' => $loan->client_id,
            'loan_id'   => $loan->id,

            'title'   => $title,
            'message' => $message,

            'type'     => $type,
            'priority' => $type === 'payment_overdue' ? 'high' : 'normal',

            'remind_at'  => $remindAt,
            'expires_at' => $remindAt->copy()->addHours(6), // opcional
            'status'     => 'pending',
            'channel'    => 'system',
            'channel_status' => 'pending',

            'created_by' => $loan->created_by,
            'updated_by' => $loan->updated_by,
        ]);

        return true;
    }

    private function buildTitle($loan, string $type, ?Carbon $finishDate = null): string
    {
        return match ($type) {
            'payment_due'     => "Pago por vencer - {$loan->loan_code}",
            'payment_overdue' => "Pago vencido - {$loan->loan_code}",
            'loan_finish'     => "Préstamo por finalizar - {$loan->loan_code}",
            default           => "Recordatorio - {$loan->loan_code}",
        };
    }

    private function buildMessage($loan, string $type, ?Carbon $finishDate = null): string
    {
        return match ($type) {
            'payment_due' => "El préstamo {$loan->loan_code} tiene un pago por vencer pronto (vencimiento: {$loan->due_date}).",
            'payment_overdue' => "El préstamo {$loan->loan_code} tiene un pago vencido (vencimiento: {$loan->due_date}).",
            'loan_finish' => "El préstamo {$loan->loan_code} está por finalizar (fin estimado: " . ($finishDate?->toDateString() ?? '—') . ").",
            default => "Recordatorio automático para el préstamo {$loan->loan_code}.",
        };
    }
}
