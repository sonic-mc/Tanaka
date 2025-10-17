@extends('layouts.app')

@section('header')
    Progress Reports
@endsection

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ session('activeTab', '#create') === '#create' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab">
                Create Report
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ session('activeTab') === '#view' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#view" type="button" role="tab">
                View Reports
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reportTabsContent">
        <!-- Create Report Tab -->
        <div class="tab-pane fade {{ session('activeTab', '#create') === '#create' ? 'show active' : '' }}" id="create" role="tabpanel">
            <form method="POST" action="{{ route('progress-reports.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select patient</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" @selected(old('patient_id')==$patient->id)>
                                    {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Clinician</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                    </div>
                </div>

                <h6 class="mt-4">1. Symptom Severity</h6>
                @foreach(['depressed_mood', 'anxiety', 'suicidal_ideation'] as $symptom)
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $symptom)) }} Intensity (1–10)</label>
                            <input type="range" name="{{ $symptom }}" min="1" max="10" class="form-range" value="{{ old($symptom) }}">
                        </div>
                        <div class="col-md-8">
                            <small class="text-muted">Use the slider to set severity.</small>
                        </div>
                    </div>
                @endforeach

                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Hallucinations</label>
                        <select name="hallucinations" class="form-select">
                            <option value="">None</option>
                            <option value="auditory" @selected(old('hallucinations')==='auditory')>Auditory</option>
                            <option value="visual" @selected(old('hallucinations')==='visual')>Visual</option>
                            <option value="other" @selected(old('hallucinations')==='other')>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Delusions</label>
                        <select name="delusions" class="form-select">
                            <option value="">None</option>
                            <option value="paranoid" @selected(old('delusions')==='paranoid')>Paranoid</option>
                            <option value="grandiose" @selected(old('delusions')==='grandiose')>Grandiose</option>
                            <option value="other" @selected(old('delusions')==='other')>Other</option>
                        </select>
                    </div>
                </div>

                <h6 class="mt-4">2. Functional Status</h6>
                @foreach(['self_care', 'work_school', 'social_interactions', 'daily_activities'] as $domain)
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $domain)) }}</label>
                            <select name="{{ $domain }}" class="form-select">
                                <option value="">—</option>
                                <option value="Independent">Independent</option>
                                <option value="Needs support">Needs support</option>
                                <option value="Dependent">Dependent</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <!-- Optional comments could be added with a parallel text input if needed -->
                        </div>
                    </div>
                @endforeach

                <h6 class="mt-4">3. Cognitive & Emotional Functioning</h6>
                @foreach(['attention', 'memory', 'decision_making', 'emotional_regulation', 'insight'] as $aspect)
                    <div class="mb-2">
                        <label class="form-label">{{ ucfirst(str_replace('_', ' ', $aspect)) }}</label>
                        <input type="text" name="{{ $aspect }}" class="form-control" value="{{ old($aspect) }}">
                    </div>
                @endforeach

                <h6 class="mt-4">4. Behavioral Observations</h6>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Medication Adherence</label>
                        <select name="medication_adherence" class="form-select">
                            <option value="">Unknown</option>
                            <option value="1" @selected(old('medication_adherence')==='1')>Yes</option>
                            <option value="0" @selected(old('medication_adherence')==='0')>No</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Therapy Engagement</label>
                        <select name="therapy_engagement" class="form-select">
                            <option value="">Unknown</option>
                            <option value="1" @selected(old('therapy_engagement')==='1')>Yes</option>
                            <option value="0" @selected(old('therapy_engagement')==='0')>No</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Risk Behaviors</label>
                        <select name="risk_behaviors" class="form-select">
                            <option value="">None</option>
                            <option value="self-harm">Self-harm</option>
                            <option value="aggression">Aggression</option>
                            <option value="substance use">Substance use</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sleep Activity Patterns</label>
                        <input type="text" name="sleep_activity_patterns" class="form-control" value="{{ old('sleep_activity_patterns') }}" placeholder="e.g. insomnia, hypersomnia">
                    </div>
                </div>

                <h6 class="mt-4">5. Physical Health</h6>
                <div class="row mb-2">
                    <div class="col-md-3"><input type="number" step="0.01" name="weight" class="form-control" placeholder="Weight (kg)" value="{{ old('weight') }}"></div>
                    <div class="col-md-3"><input type="text" name="vital_signs" class="form-control" placeholder="Vital Signs" value="{{ old('vital_signs') }}"></div>
                    <div class="col-md-3"><input type="text" name="medication_side_effects" class="form-control" placeholder="Side Effects" value="{{ old('medication_side_effects') }}"></div>
                    <div class="col-md-3"><input type="text" name="general_health" class="form-control" placeholder="General Health" value="{{ old('general_health') }}"></div>
                </div>

                <h6 class="mt-4">6. Social Support & Environment</h6>
                @foreach(['family_support', 'peer_support', 'housing_stability', 'access_to_services'] as $support)
                    <div class="mb-2">
                        <label class="form-label">{{ ucfirst(str_replace('_', ' ', $support)) }}</label>
                        <input type="text" name="{{ $support }}" class="form-control" value="{{ old($support) }}">
                    </div>
                @endforeach

                <h6 class="mt-4">7. Risk Assessment</h6>
                <div class="row mb-2">
                    @foreach(['suicide_risk', 'homicide_risk', 'self_neglect_risk', 'vulnerability_risk'] as $risk)
                        <div class="col-md-3">
                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $risk)) }}</label>
                            <select name="{{ $risk }}" class="form-select">
                                <option value="">—</option>
                                <option value="1" @selected(old($risk)=='1')>Low</option>
                                <option value="2" @selected(old($risk)=='2')>Moderate</option>
                                <option value="3" @selected(old($risk)=='3')>High</option>
                            </select>
                        </div>
                    @endforeach
                </div>

                <h6 class="mt-4">8. Treatment Goals</h6>
                @for($i = 1; $i <= 3; $i++)
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <input type="text" name="treatment_goals[{{ $i }}][goal]" class="form-control" placeholder="Goal {{ $i }}">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="1" name="treatment_goals[{{ $i }}][baseline]" class="form-control" placeholder="Baseline">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="1" name="treatment_goals[{{ $i }}][current]" class="form-control" placeholder="Current">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="treatment_goals[{{ $i }}][notes]" class="form-control" placeholder="Notes">
                        </div>
                    </div>
                @endfor

                <h6 class="mt-4">Clinician Summary / Recommendations</h6>
                <textarea name="notes" class="form-control mb-3" rows="4" placeholder="Summary, observations, and recommendations...">{{ old('notes') }}</textarea>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Submit Report</button>
                </div>
            </form>
        </div>

        <!-- View Reports Tab -->
        <div class="tab-pane fade {{ session('activeTab') === '#view' ? 'show active' : '' }}" id="view" role="tabpanel">
            <form method="GET" action="{{ route('progress-reports.index') }}" class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label">Select Patient</label>
                    <select name="patient_id" class="form-select" required>
                        <option value="">Select patient</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="submit" class="btn btn-outline-primary w-100">View Progress</button>
                </div>
            </form>

            @if(isset($selectedPatient))
                <h5 class="mb-3">Progress Reports for {{ $selectedPatient->first_name }} {{ $selectedPatient->last_name }}</h5>

                @forelse($filteredReports as $report)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <span>{{ $report->created_at->format('Y-m-d') }} — {{ $report->reporter->name }}</span>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('progress-reports.show', $report) }}">View details</a>
                        </div>
                        <div class="card-body">
                            <p><strong>Depressed Mood:</strong>
                                <span class="badge bg-{{ $report->depressed_mood >= 7 ? 'danger' : ($report->depressed_mood >= 4 ? 'warning' : 'success') }}">
                                    {{ $report->depressed_mood ?? '—' }}/10
                                </span>
                            </p>
                            <p><strong>Suicide Risk:</strong>
                                @if($report->suicide_risk == 3)
                                    <span class="badge bg-danger">High</span>
                                @elseif($report->suicide_risk == 2)
                                    <span class="badge bg-warning text-dark">Moderate</span>
                                @elseif($report->suicide_risk == 1)
                                    <span class="badge bg-success">Low</span>
                                @else
                                    <span class="badge bg-secondary">—</span>
                                @endif
                            </p>
                            <p class="mb-0"><strong>Notes:</strong> {{ $report->notes ? Str::limit($report->notes, 160) : '—' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No reports found for this patient.</p>
                @endforelse
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');

        // Restore last active tab
        const lastTab = localStorage.getItem('activeTab');
        if (lastTab) {
            const trigger = document.querySelector(`[data-bs-target="${lastTab}"]`);
            if (trigger) {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        }

        // Save active tab on change
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function (event) {
                const target = event.target.getAttribute('data-bs-target');
                localStorage.setItem('activeTab', target);
            });
        });

        // If server asked to open a tab (flash), honor it
        @if(session('activeTab'))
            const serverTab = "{{ session('activeTab') }}";
            const trigger = document.querySelector(`[data-bs-target="${serverTab}"]`);
            if (trigger) { bootstrap.Tab.getOrCreateInstance(trigger).show(); }
        @endif
    });
</script>
@endsection
