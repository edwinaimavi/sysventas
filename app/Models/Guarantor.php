<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guarantor extends Model
{
    /** @use HasFactory<\Database\Factories\GuarantorFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'is_external',
        'document_type',
        'document_number',
        'full_name',
        'first_name',
        'last_name',
        'company_name',
        'ruc',
        'phone',
        'alt_phone',
        'email',
        'address',
        'relationship',
        'occupation',
        'photo',
        'status',
        'created_by',
        'updated_by',
    ];

    // Relaciones
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

}
