<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    /** @use HasFactory<\Database\Factories\BranchFactory> */
    use HasFactory;

    // app/Models/Branch.php
protected $fillable = [
    'code',
    'name',
    'address',
    'phone',
    'email',
    'manager_user_id',
    'is_active',
];

}
