<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory;

     protected $fillable = [
        'client_id',
        'guarantor_id',
        'loan_id',
        'collateral_id',
        'type',
        'brand',
        'model',
        'year',
        'plate_number',
        'vin',
        'engine_number',
        'color',
        'mileage',
        'appraised_value',
        'condition',
        'description',
        'registration_doc',
        'photo',
        'status',
    ];

    protected $casts = [
        'appraised_value' => 'decimal:2',
        'mileage' => 'integer',
    ];

    // Relaciones
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function guarantor()
    {
        return $this->belongsTo(Guarantor::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function collateral()
    {
        return $this->belongsTo(Collateral::class);
    }
}
