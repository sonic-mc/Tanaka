@extends('layouts.app')

@section('header')
    Progress Report Details
@endsection

@section('content')
<div class="container">
    <h4>Patient: {{ $report->patient->first_name }} {{ $report->patient->last_name }} ({{ $report->patient->patient_code }})</h4>
    <p>Reported by: {{ $report->reporter->name }}</p>
    <p>Date: {{ $report->created_at->format('Y-m-d') }}</p>

    <hr>
    <h5>1. Symptom Severity</h5>
    <ul>
        <li>Depressed Mood: {{ $report->depressed_mood ?? '—' }}/10</li>
        <li>Anxiety: {{ $report->anxiety ?? '—' }}/10</li>
        <li>Suicidal Ideation: {{ $report->suicidal_ideation ?? '—' }}/10</li>
        <li>Hallucinations: {{ $report->hallucinations ?? 'None' }}</li>
        <li>Delusions: {{ $report->delusions ?? 'None' }}</li>
        <li>Sleep Disturbance: {{ $report->sleep_disturbance ?? '—' }}/10</li>
        <li>Appetite Changes: {{ $report->appetite_changes ?? '—' }}/10</li>
    </ul>

    <h5>2. Functional Status</h5>
    <ul>
        <li>Self Care: {{ $report->self_care ?? '—' }}</li>
        <li>Work/School: {{ $report->work_school ?? '—' }}</li>
        <li>Social Interactions: {{ $report->social_interactions ?? '—' }}</li>
        <li>Daily Activities: {{ $report->daily_activities ?? '—' }}</li>
    </ul>

    <h5>3. Cognitive & Emotional Functioning</h5>
    <ul>
        <li>Attention: {{ $report->attention ?? '—' }}</li>
        <li>Memory: {{ $report->memory ?? '—' }}</li>
        <li>Decision Making: {{ $report->decision_making ?? '—' }}</li>
        <li>Emotional Regulation: {{ $report->emotional_regulation ?? '—' }}</li>
        <li>Insight: {{ $report->insight ?? '—' }}</li>
    </ul>

    <h5>4. Behavioral Observations</h5>
    <ul>
        <li>Medication Adherence: 
            @if($report->medication_adherence === null) — 
            @else {{ $report->medication_adherence ? 'Yes' : 'No' }} 
            @endif
        </li>
        <li>Therapy Engagement:
            @if($report->therapy_engagement === null) — 
            @else {{ $report->therapy_engagement ? 'Yes' : 'No' }} 
            @endif
        </li>
        <li>Risk Behaviors: {{ $report->risk_behaviors ?: 'None' }}</li>
        <li>Sleep Activity Patterns: {{ $report->sleep_activity_patterns ?? '—' }}</li>
    </ul>

    <h5>5. Physical Health</h5>
    <ul>
        <li>Weight: {{ $report->weight !== null ? number_format($report->weight, 2) . ' kg' : '—' }}</li>
        <li>Vital Signs: {{ $report->vital_signs ?? '—' }}</li>
        <li>Medication Side Effects: {{ $report->medication_side_effects ?? '—' }}</li>
        <li>General Health: {{ $report->general_health ?? '—' }}</li>
    </ul>

    <h5>6. Social Support & Environment</h5>
    <ul>
        <li>Family Support: {{ $report->family_support ?? '—' }}</li>
        <li>Peer Support: {{ $report->peer_support ?? '—' }}</li>
        <li>Housing Stability: {{ $report->housing_stability ?? '—' }}</li>
        <li>Access to Services: {{ $report->access_to_services ?? '—' }}</li>
    </ul>

    <h5>7. Risk Assessment</h5>
    <ul>
        <li>Suicide Risk: {{ [1=>'Low',2=>'Moderate',3=>'High'][$report->suicide_risk] ?? '—' }}</li>
        <li>Homicide Risk: {{ [1=>'Low',2=>'Moderate',3=>'High'][$report->homicide_risk] ?? '—' }}</li>
        <li>Self Neglect Risk: {{ [1=>'Low',2=>'Moderate',3=>'High'][$report->self_neglect_risk] ?? '—' }}</li>
        <li>Vulnerability Risk: {{ [1=>'Low',2=>'Moderate',3=>'High'][$report->vulnerability_risk] ?? '—' }}</li>
    </ul>

    <h5>8. Treatment Goals</h5>
    @if(!empty($report->treatment_goals))
        <ul>
            @foreach($report->treatment_goals as $goal)
                <li>
                    <strong>{{ $goal['goal'] ?? '—' }}</strong>
                    | Baseline: {{ $goal['baseline'] ?? '—' }}
                    | Current: {{ $goal['current'] ?? '—' }}
                    <br>{{ $goal['notes'] ?? '' }}
                </li>
            @endforeach
        </ul>
    @else
        <p>No treatment goals recorded.</p>
    @endif

    <h5>Clinician Notes</h5>
    <p>{{ $report->notes ?? '—' }}</p>
</div>
@endsection
