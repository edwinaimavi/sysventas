<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerCapital extends Model
{
    protected $fillable = [
        'partner_id',
        'cash_box_id',
        'amount',
        'notes',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function cashBox()
    {
        return $this->belongsTo(CashBox::class);
    }
}
