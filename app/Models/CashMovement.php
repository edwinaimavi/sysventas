<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    protected $fillable = [
        'cash_box_id',
        'branch_id',
        'type',
        'concept',
        'amount',
        'notes',
        'user_id',
        'reference_table',
        'reference_id',
    ];

    public function cashBox()
    {
        return $this->belongsTo(CashBox::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    /* 
    public function cashMovement()
    {
        return $this->morphOne(CashMovement::class, 'reference');
    }
    public function reference()
    {
        return $this->morphTo();
    } */
    // ✅ SIN where aquí
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'reference_id');
    }

    // ✅ SIN where aquí
    public function payment()
    {
        return $this->belongsTo(LoanPayment::class, 'reference_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
