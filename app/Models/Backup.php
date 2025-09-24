<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'file_path', 'created_by', 'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'restored_at' => 'datetime',
    ];
    

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

