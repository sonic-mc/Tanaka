<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    protected $fillable = [
        'patient_id', 'reported_by', 'incident_date', 'description'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}

