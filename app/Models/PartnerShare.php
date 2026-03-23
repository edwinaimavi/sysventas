<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerShare extends Model
{
    protected $fillable = [
        'partner_id',
        'percentage',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
