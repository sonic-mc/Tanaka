<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false; // we use custom 'timestamp' column

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'severity',   // 'info' | 'warning' | 'critical'
        'description',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'description' => 'string', // contains JSON string with message/context
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
