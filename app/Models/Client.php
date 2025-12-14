<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',              // Sucursal donde se registró el cliente
        'user_id',                // Usuario que registró o atiende al cliente
        'document_type',          // Tipo de documento
        'document_number',        // Número de documento
        'full_name',              // Nombre completo del cliente
        'first_name',             // Nombres
        'last_name',              // Apellidos
        'birth_date',             // Fecha de nacimiento
        'gender',                 // Género (M/F/Otro)
        'marital_status',         // Estado civil
        'occupation',             // Ocupación o profesión
        'company_name',           // Nombre de empresa (si es persona jurídica)
        'ruc',                    // RUC (si aplica)
        'email',                  // Correo electrónico
        'phone',                  // Teléfono
        'photo',                  // Foto o URL
        'status',                 // Estado activo/inactivo
        'credit_score',           // Puntaje crediticio
    ];

     // Relaciones
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientContacts()
    {
        return $this->hasMany(\App\Models\ClientContact::class);
    }
}
