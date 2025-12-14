<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    /** @use HasFactory<\Database\Factories\ClientContactFactory> */
    use HasFactory;

     protected $fillable = [
        'client_id',
        'contact_type',
        'address',
        'district',
        'province',
        'department',
        'reference',
        'phone',
        'alt_phone',
        'email',
        'contact_name',
        'relationship',
        'is_primary',
    ];

    // Opcional pero recomendable: castear is_primary a boolean
    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
