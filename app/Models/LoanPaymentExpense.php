<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPaymentExpense extends Model
{
    use HasFactory;

    protected $table = 'loan_payment_expenses';

    protected $fillable = [
        'loan_payment_id',
        'branch_id',
        'user_id',
        'expense_amount',
        'expense_type',
        'expense_description',
    ];

    public function payment()
    {
        return $this->belongsTo(LoanPayment::class, 'loan_payment_id');
    }
}
