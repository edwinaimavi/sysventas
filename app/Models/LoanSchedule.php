<?php

// app/Models/LoanSchedule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    protected $fillable = [
        'loan_id',
        'installment_no',
        'due_date',
        'opening_balance',
        'interest',
        'amortization',
        'payment',
        'closing_balance',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
