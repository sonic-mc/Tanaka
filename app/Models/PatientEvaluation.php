<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\AutoGradesEvaluation;

class PatientEvaluation extends Model
{
    use SoftDeletes, AutoGradesEvaluation;

    public const TYPE_INITIAL   = 'initial';
    public const TYPE_FOLLOW_UP = 'follow-up';
    public const TYPE_EMERGENCY = 'emergency';

    public const DECISION_ADMIT      = 'admit';
    public const DECISION_OUTPATIENT = 'outpatient';
    public const DECISION_REFER      = 'refer';
    public const DECISION_MONITOR    = 'monitor';

    public const SEVERITY_MILD     = 'mild';
    public const SEVERITY_MODERATE = 'moderate';
    public const SEVERITY_SEVERE   = 'severe';
    public const SEVERITY_CRITICAL = 'critical';

    public const RISK_LOW    = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH   = 'high';

    protected $guarded = [];

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

    public function admission()
    {
        return $this->hasOne(Admission::class, 'evaluation_id');
    }

    public function determineGrading(): void
    {
        app(\App\Services\EvaluationGradingService::class)->apply($this);
    }

    // Scopes
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
