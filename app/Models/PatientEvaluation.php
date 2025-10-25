<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\AutoGradesEvaluation;

class PatientEvaluation extends Model
{
    use SoftDeletes, AutoGradesEvaluation;

    protected $fillable = [
        'patient_id',
        'psychiatrist_id',
        'evaluation_date',
        'evaluation_type',
        'presenting_complaints',
        'clinical_observations',
        'diagnosis',
        'recommendations',
        'decision',
        'requires_admission',
        'admission_trigger_notes',
        'decision_made_at',
        'created_by',
        'last_modified_by',
        // Grading fields
        'severity_level',
        'risk_level',
        'priority_score',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'requires_admission' => 'boolean',
        'decision_made_at' => 'datetime',
        'priority_score' => 'integer',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientDetail::class, 'patient_id');
    }

    public function psychiatrist()
    {
        return $this->belongsTo(User::class, 'psychiatrist_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastModifier()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    /**
     * Legacy method name preserved: compute and persist grading now via service.
     */
    public function determineGrading(): void
    {
        app(\App\Services\EvaluationGradingService::class)->apply($this);
    }

    // Scopes used elsewhere...
    public function scopeOfType($query, ?string $type)
    {
        if ($type) {
            $query->where('evaluation_type', $type);
        }
        return $query;
    }

    public function scopeOfDecision($query, ?string $decision)
    {
        if ($decision) {
            $query->where('decision', $decision);
        }
        return $query;
    }

    public function scopeDateBetween($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('evaluation_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('evaluation_date', '<=', $to);
        }
        return $query;
    }

    public function scopeSearch($query, ?string $q)
    {
        if ($q) {
            $query->whereHas('patient', function ($sub) use ($q) {
                $sub->where('patient_code', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('national_id_number', 'like', "%{$q}%")
                    ->orWhere('passport_number', 'like', "%{$q}%");
            });
        }
        return $query;
    }
}
