<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // audit_logs uses a custom "timestamp" column; no created_at/updated_at
    public $timestamps = false;

    protected $table = 'audit_logs';

    // If your table has origin_ip, keep it here; otherwise remove it.
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'module',
        'severity',   // tinyint in DB: 0=info, 1=warning, 2=error
        'timestamp',  // datetime
        // 'origin_ip',  // nullable, optional
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'severity'  => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Log an audit entry.
     * $severity can be "info"|"warning"|"error" or 0|1|2
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?string $module = null,
        $severity = 'info',
        ?string $originIp = null
    ): void {
        static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'description'=> $description,
            'module'     => $module,
            'severity'   => static::normalizeSeverity($severity),
            'timestamp'  => now(),
            'origin_ip'  => $originIp ?? request()->ip(),
        ]);
    }

    /**
     * Normalize severity into numeric levels expected by DB.
     * 0=info, 1=warning, 2=error
     */
    protected static function normalizeSeverity($severity): int
    {
        if (is_numeric($severity)) {
            $n = (int) $severity;
            return in_array($n, [0, 1, 2], true) ? $n : 0;
        }

        return match (strtolower((string) $severity)) {
            'info' => 0,
            'warning', 'warn' => 1,
            'error', 'err' => 2,
            default => 0,
        };
    }
}
