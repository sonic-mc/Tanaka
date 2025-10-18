<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TherapySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'clinician_id',
        'session_start',
        'session_end',
        'session_type',
        'mode',
        'session_number',
        'presenting_issues',
        'mental_status_exam',
        'interventions',
        'observations',
        'plan',
        'goals_progress',
        'status',
    ];

    protected $casts = [
        'session_start' => 'datetime',
        'session_end' => 'datetime',
        'goals_progress' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(\App\Models\Patient::class);
    }

    public function clinician()
    {
        return $this->belongsTo(\App\Models\User::class, 'clinician_id');
    }
}
