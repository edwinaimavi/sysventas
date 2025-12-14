<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    /** @use HasFactory<\Database\Factories\LoanPaymentFactory> */
    use HasFactory;
    protected $fillable = [
        'loan_id',
        'branch_id',
        'user_id',
        'payment_code',
        'payment_date',
        'amount',
        'payment_type',
        'capital',
        'interest',
        'late_fee',
        'method',
        'reference',
        'receipt_number',
        'receipt_file',
        'status',
        'remaining_balance',
        'notes',
    ];

    // 👇 AÑADE ESTO
    protected $casts = [
        'payment_date' => 'date',
    ];

    // Relaciones
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
