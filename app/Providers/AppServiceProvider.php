<?php

namespace App\Observers;

use App\Models\Loan;
use App\Models\Reminder;
use Illuminate\Support\Facades\Auth;

class LoanObserver
{
    public function created(Loan $loan): void
    {
        $this->generateAutoReminders($loan);
    }

    public function updated(Loan $loan): void
    {
        // si cambian fechas clave o estado, regeneramos (opcional)
        if ($loan->wasChanged(['status', 'start_date', 'end_date', 'next_payment_date'])) {
            $this->generateAutoReminders($loan, true);
        }
    }

    private function generateAutoReminders(Loan $loan, bool $rebuild = false): void
    {
        // Ajusta estos campos a los que tú tengas realmente en loans:
        // - next_payment_date (fecha próxima cuota)
        // - end_date (fecha fin del préstamo)
        // Si no existen, dime cómo se llaman y lo adaptamos.

        $branchId = $loan->branch_id;
        $userId   = $loan->user_id ?? Auth::id(); // destinatario (ajusta si corresponde)

        // (opcional) si rebuild: elimina recordatorios automáticos pendientes del préstamo
        if ($rebuild) {
            Reminder::where('loan_id', $loan->id)
                ->whereIn('type', ['payment_due', 'payment_overdue', 'loan_finish'])
                ->where('status', 'pending')
                ->delete();
        }

        // 1) Pago por vencer (2 días antes)
        if (!empty($loan->next_payment_date)) {
            $remindAt = \Carbon\Carbon::parse($loan->next_payment_date)->subDays(2)->setTime(9, 0);

            Reminder::create([
                'branch_id' => $branchId,
                'user_id'   => $userId,
                'client_id' => $loan->client_id,
                'loan_id'   => $loan->id,
                'title'     => "Pago por vencer: {$loan->loan_code}",
                'message'   => "Recordar al cliente sobre el pago próximo.",
                'type'      => 'payment_due',
                'priority'  => 'normal',
                'remind_at' => $remindAt,
                'status'    => 'pending',
                'channel'   => 'system',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }

        // 2) Pago vencido (1 día después de next_payment_date)
        if (!empty($loan->next_payment_date)) {
            $remindAt = \Carbon\Carbon::parse($loan->next_payment_date)->addDay()->setTime(9, 0);

            Reminder::create([
                'branch_id' => $branchId,
                'user_id'   => $userId,
                'client_id' => $loan->client_id,
                'loan_id'   => $loan->id,
                'title'     => "Pago vencido: {$loan->loan_code}",
                'message'   => "Verificar si el cliente ya pagó. Si no, aplicar seguimiento.",
                'type'      => 'payment_overdue',
                'priority'  => 'high',
                'remind_at' => $remindAt,
                'status'    => 'pending',
                'channel'   => 'system',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }

        // 3) Fin de préstamo
        if (!empty($loan->end_date)) {
            $remindAt = \Carbon\Carbon::parse($loan->end_date)->subDays(1)->setTime(10, 0);

            Reminder::create([
                'branch_id' => $branchId,
                'user_id'   => $userId,
                'client_id' => $loan->client_id,
                'loan_id'   => $loan->id,
                'title'     => "Préstamo por finalizar: {$loan->loan_code}",
                'message'   => "Revisar saldo final y confirmar cierre.",
                'type'      => 'loan_finish',
                'priority'  => 'normal',
                'remind_at' => $remindAt,
                'status'    => 'pending',
                'channel'   => 'system',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }
    }
}
