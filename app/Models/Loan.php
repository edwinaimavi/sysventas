<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    /** @use HasFactory<\Database\Factories\LoanFactory> */
    use HasFactory;


    protected $fillable = [
        'client_id',
        'guarantor_id',
        'branch_id',
        'user_id',
        'loan_code',
        'amount',
        'term_months',
        'interest_rate',
        'monthly_payment',
        'total_payable',
        'disbursement_date',
        'due_date',          // 👈 NUEVO
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'disbursement_date' => 'date',
        'due_date'          => 'date',   // 👈 NUEVO
    ];

    /* =======================
     *   RELACIONES
     * ======================= */

    public function loan()
    {
        return $this->belongsTo(\App\Models\Loan::class);
    }

    // Cliente solicitante
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Garante
    public function guarantor()
    {
        return $this->belongsTo(Guarantor::class);
    }

    // Sucursal
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Usuario que registra
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disbursements()
    {
        return $this->hasMany(LoanDisbursement::class);
    }
}
