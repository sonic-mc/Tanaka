<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingStatement extends Model
{
    public $timestamps = false; // we have explicit last_updated timestamp column

    protected $fillable = [
        'patient_id',
        'total_amount',
        'outstanding_balance',
        'last_updated',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
