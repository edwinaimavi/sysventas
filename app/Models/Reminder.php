<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'user_id',
        'client_id',
        'loan_id',
        'title',
        'message',
        'type',
        'priority',
        'remind_at',
        'expires_at',
        'created_for_date',
        'status',
        'channel',
        'channel_status',
        'channel_response',
        'is_read',
        'read_at',
        'triggered_at',
        'sent_at',
        'cancelled_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'remind_at'     => 'datetime',
        'expires_at'    => 'datetime',
        'created_for_date' => 'date',
        'triggered_at'  => 'datetime',
        'sent_at'       => 'datetime',
        'cancelled_at'  => 'datetime',
        'read_at'       => 'datetime',
        'is_read'       => 'boolean',
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
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
