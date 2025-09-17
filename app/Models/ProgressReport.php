<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    protected $fillable = [
        'patient_id', 'reported_by', 'report_date',
        'behavior_notes', 'medication_response',
        'attendance_days', 'progress_score'
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
