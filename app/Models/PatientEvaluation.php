<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientEvaluation extends Model
{
    use HasFactory;

    protected $table = 'patient_evaluations';

    protected $fillable = [
        'patient_id',
        'psychiatrist_id',
        'evaluation_date',
        'evaluation_type',
        'presenting_complaints',
        'clinical_observations',
        'diagnosis',
        'recommendations',
        'severity_level',
        'risk_level',
        'priority_score',
        'decision',
        'requires_admission',
        'admission_trigger_notes',
        'decision_made_at',
        'created_by',
        'last_modified_by',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'decision_made_at' => 'datetime',
        'requires_admission' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientDetail::class, 'patient_id');
    }

    public function psychiatrist()
    {
        return $this->belongsTo(\App\Models\User::class, 'psychiatrist_id');
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'last_modified_by');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'psychiatrist_id');
    }


    public function lastModifier()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
