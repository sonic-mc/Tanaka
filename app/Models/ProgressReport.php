<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    protected $fillable = [
        'patient_id',
        'reported_by',

        // Symptom Severity
        'depressed_mood',
        'anxiety',
        'hallucinations',
        'delusions',
        'sleep_disturbance',
        'appetite_changes',
        'suicidal_ideation',

        // Functional Status
        'self_care',
        'work_school',
        'social_interactions',
        'daily_activities',

        // Cognitive & Emotional Functioning
        'attention',
        'memory',
        'decision_making',
        'emotional_regulation',
        'insight',

        // Behavioral Observations
        'medication_adherence',
        'therapy_engagement',
        'risk_behaviors',
        'sleep_activity_patterns',

        // Physical Health
        'weight',
        'vital_signs',
        'medication_side_effects',
        'general_health',

        // Social Support & Environment
        'family_support',
        'peer_support',
        'housing_stability',
        'access_to_services',

        // Risk Assessment
        'suicide_risk',
        'homicide_risk',
        'self_neglect_risk',
        'vulnerability_risk',

        // Treatment Goals (JSON)
        'treatment_goals',

        // Notes
        'notes',
    ];

    protected $casts = [
        // numbers
        'depressed_mood'        => 'integer',
        'anxiety'               => 'integer',
        'sleep_disturbance'     => 'integer',
        'appetite_changes'      => 'integer',
        'suicidal_ideation'     => 'integer',
        'suicide_risk'          => 'integer',
        'homicide_risk'         => 'integer',
        'self_neglect_risk'     => 'integer',
        'vulnerability_risk'    => 'integer',
        'weight'                => 'decimal:2',

        // booleans
        'medication_adherence'  => 'boolean',
        'therapy_engagement'    => 'boolean',

        // json
        'treatment_goals'       => 'array',
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
