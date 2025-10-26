<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{

    protected $table = 'incidents_reports';
    
    protected $fillable = [
        'patient_id', 'reported_by', 'incident_date', 'description'
    ];

    public function patient()
    {
        return $this->belongsTo(PatientDetail::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by'); // 'reported_by' is the foreign key in incident_reports table
    }
}

