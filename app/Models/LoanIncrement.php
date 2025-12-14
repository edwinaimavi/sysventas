<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanIncrement extends Model
{
    /** @use HasFactory<\Database\Factories\LoanIncrementFactory> */
    use HasFactory;
    protected $table = 'loan_increments';

    protected $fillable = [
        'loan_id',
        'branch_id',
        'user_id',
        'old_amount',
        'increment_amount',
        'new_amount',
        'notes',
    ];

    // Relaciones opcionales
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
