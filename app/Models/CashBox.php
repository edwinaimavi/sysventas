<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBox extends Model
{
    protected $table = 'cash_boxes';
    protected $fillable = [
        'branch_id',
        'opened_at',
        'closed_at',
        'opening_amount',
        'closing_amount',
        'status',
        'opened_by',
        'closed_by',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class);
    }

   /*  public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    } */

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    

    /* public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    } */
}
