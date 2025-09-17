<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discharge extends Model
{
    protected $fillable = [
        'patient_id', 'discharged_by', 'discharge_date',
        'discharge_summary', 'final_risk_level'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function dischargedBy()
    {
        return $this->belongsTo(User::class, 'discharged_by');
    }
}

