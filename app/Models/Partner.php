<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'full_name',
        'document_type',
        'document_number',
        'phone',
        'email',
        'active',
    ];

    public function capitals()
    {
        return $this->hasMany(PartnerCapital::class);
    }

    public function share()
    {
        return $this->hasOne(PartnerShare::class);
    }
}
