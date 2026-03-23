<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Loan extends Model
{
    /** @use HasFactory<\Database\Factories\LoanFactory> */
    use HasFactory;


    protected $fillable = [
        'client_id',
        'guarantor_id',
        'branch_id',
        'user_id',
        'loan_code',
        'amount',
        'term_months',
        'interest_rate',
        'monthly_payment',
        'total_payable',
        'disbursement_date',
        'due_date',          // 👈 NUEVO
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'disbursement_date' => 'date',
        'due_date'          => 'date',   // 👈 NUEVO
    ];

    /* =======================
     *   RELACIONES
     * ======================= */

    /*     public function loan()
    {
        return $this->belongsTo(\App\Models\Loan::class);
    } */

    // Cliente solicitante
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Garante
    public function guarantor()
    {
        return $this->belongsTo(Guarantor::class);
    }

    // Sucursal
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Usuario que registra
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disbursements()
    {
        return $this->hasMany(LoanDisbursement::class);
    }
    public function payments()
    {
        return $this->hasMany(\App\Models\LoanPayment::class);
    }


    public function refinances()
    {
        return $this->hasMany(LoanRefinancing::class);
    }

    public function remainingBalance(): float
    {
        $branchId = $this->branch_id;

        $paid = LoanPayment::where('loan_id', $this->id)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->sum('amount');

        $remaining = (float)$this->total_payable - (float)$paid;
        return $remaining < 0 ? 0 : round($remaining, 2);
    }

    public function activeRefinance()
    {
        return $this->hasOne(LoanRefinancing::class)->where('status', 'active');
    }

    public function hasActiveRefinance(): bool
    {
        return $this->activeRefinance()->exists();
    }

    public function isExpired(): bool
    {
        if (!$this->due_date) return false;

        $today = Carbon::today();
        $due   = Carbon::parse($this->due_date);

        return $today->gt($due) && $this->status === 'disbursed' && $this->remainingBalance() > 0.009;
    }

    // app/Models/Loan.php
    public function schedules()
    {
        return $this->hasMany(\App\Models\LoanSchedule::class)->orderBy('installment_no');
    }
}
