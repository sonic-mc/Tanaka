<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    // Your table only has created_at and restored_at, so disable Eloquent timestamps
    public $timestamps = false;

    protected $fillable = [
        'file_path',
        'filename',
        'type',        // 'database' | 'files' | 'full'
        'status',      // 'pending' | 'completed' | 'failed' | 'restored'
        'notes',
        'created_by',
        'origin_ip',
        'created_at',
        'restored_at',
    ];

    protected $casts = [
        'created_at'  => 'datetime',
        'restored_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
