<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'module',
        'severity',
        'timestamp',
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public static function log(string $action, ?string $description = null, ?string $module = null, string $severity = 'info'): void
{
    self::create([
        'user_id'    => auth()->id(),
        'action'     => $action,
        'description'=> $description,
        'module'     => $module,
        'severity'   => $severity,
        'timestamp'  => now(),
    ]);
}


    
}
