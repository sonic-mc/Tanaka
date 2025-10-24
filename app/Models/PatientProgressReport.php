<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientProgressReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'patient_progress_reports';

    protected $fillable = [
        'patient_id',
        'admission_id',
        'evaluation_id',
        'clinician_id',
        'report_date',
        'gaf_score',
        'phq9_score',
        'gad7_score',
        'who_das_score',
        'honos_score',
        'bprs_score',
        'cgi_severity',
        'global_severity_score',
        'functional_score',
        'risk_level',
        'risk_assessment',
        'symptom_summary',
        'observations',
        'treatment_plan',
        'medication_changes',
        'metrics',
        'attachments',
        'created_by',
        'last_modified_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'metrics' => 'array',
        'attachments' => 'array',
        'gaf_score' => 'decimal:2',
        'who_das_score' => 'decimal:2',
        'honos_score' => 'decimal:2',
        'bprs_score' => 'decimal:2',
        'global_severity_score' => 'decimal:2',
        'functional_score' => 'decimal:2',
        'phq9_score' => 'integer',
        'gad7_score' => 'integer',
        'cgi_severity' => 'integer',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(PatientDetail::class, 'patient_id');
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function evaluation()
    {
        return $this->belongsTo(PatientEvaluation::class, 'evaluation_id');
    }

    public function clinician()
    {
        return $this->belongsTo(User::class, 'clinician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper: compact key metrics used in trend charts
    public function getTrendPoint(): array
    {
        return [
            'date' => $this->report_date ? $this->report_date->toDateString() : $this->created_at->toDateString(),
            'phq9' => $this->phq9_score !== null ? (int) $this->phq9_score : null,
            'gad7' => $this->gad7_score !== null ? (int) $this->gad7_score : null,
            'global' => $this->global_severity_score !== null ? (float) $this->global_severity_score : null,
            'functional' => $this->functional_score !== null ? (float) $this->functional_score : null,
            'risk_level' => $this->risk_level,
        ];
    }

    protected static function booted()
{
    static::creating(function ($model) {
        if (empty($model->report_date)) {
            $model->report_date = now()->toDateString();
        }
    });
}

}
