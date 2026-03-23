<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRefinancing  extends Model
{
    use HasFactory;

    protected $table = 'loan_refinances'; // ✅ PON AQUÍ EL NOMBRE REAL DE TU TABLA

    protected $fillable = [
        'loan_id',
        'branch_id',
        'user_id',
        'refinance_date',
        'new_due_date',
        'new_term_months',
        'base_balance',
        'interest_rate',
        'interest_amount',
        'new_total_payable',
        'prev_total_payable',
        'prev_remaining_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'refinance_date' => 'date',
        'new_due_date' => 'date',
        'base_balance' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'new_total_payable' => 'decimal:2',
        'prev_total_payable' => 'decimal:2',
        'prev_remaining_balance' => 'decimal:2',

        'refinance_date' => 'date:Y-m-d',
        'new_due_date'   => 'date:Y-m-d',
    ];

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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
