<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    protected $fillable = [
        'patient_id', 'reported_by', 'notes', 'depressed_mood', 'anxiety', 'suicidal_ideation',
        'hallucinations','delusions','self_care','work_school','social_interactions','daily_activities',
        'attention','memory','decision_making','emotional_regulation','insight',
        'medication_adherence','therapy_engagement','risk_behaviors','sleep_activity_patterns',
        'weight','vital_signs','medication_side_effects','general_health','family_support','peer_support',
        'housing_stability','access_to_services','suicide_risk','homicide_risk','self_neglect_risk',
        'vulnerability_risk','treatment_goals','next_review_date'
    ];
    
    protected $casts = [
        'medication_adherence' => 'boolean',
        'therapy_engagement' => 'boolean',
        'treatment_goals' => 'array',
    ];
    
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
    

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

   
}
