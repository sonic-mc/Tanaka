<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    public $timestamps = false; // Your table has created_at but no updated_at

    protected $fillable = [
        'file_path',
        'filename',
        'type',
        'status',
        'notes',
        'created_by',
        'origin_ip',
        'restored_at',
        // Optional meta if you add later:
        // 'size_bytes',
        // 'checksum_sha256',
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