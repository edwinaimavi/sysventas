<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDisbursement extends Model
{
    /** @use HasFactory<\Database\Factories\LoanDisbursementFactory> */
    use HasFactory;


    protected $fillable = [
        'loan_id',
        'branch_id',
        'user_id',
        'disbursement_code',
        'amount',
        'disbursement_date',
        'method',
        'reference',
        'receipt_number',
        'receipt_file',
        'status',
        'remaining_balance',
        'notes',
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
