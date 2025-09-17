<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'file_path', 'created_by', 'created_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

