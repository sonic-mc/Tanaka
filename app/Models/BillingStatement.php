<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingStatement extends Model
{
    protected $fillable = [
        'patient_id', 'total_amount', 'outstanding_balance'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
