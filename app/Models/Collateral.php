<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
    /** @use HasFactory<\Database\Factories\CollateralFactory> */
    use HasFactory;

     protected $fillable = [
        'loan_id',
        'type',
        'description',
        'estimated_value',
        'details',
        'photo',
        'document_file',
        'status',
    ];

    protected $casts = [
        'details' => 'array',
        'estimated_value' => 'decimal:2',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
