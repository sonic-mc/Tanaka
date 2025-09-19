@extends('layouts.app')

@section('header')
    Progress Reports
@endsection

@section('content')
<div class="container">
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab">
                Create Report
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#view" type="button" role="tab">
                View Reports
            </button>
        </li>
    </ul>
    

    <div class="tab-content" id="reportTabsContent">
        <!-- Create Report Tab -->
        <div class="tab-pane fade show active" id="create" role="tabpanel">
            <form method="POST" action="{{ route('progress-reports.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Patient</label>
                        <select name="patient_id" class="form-select" required>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="report_date" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Clinician</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                        <input type="hidden" name="reported_by" value="{{ Auth::id() }}">
                    </div>
                </div>

                <h6 class="mt-4">1. Symptom Severity</h6>
                @foreach(['depressed_mood', 'anxiety', 'suicidal_ideation'] as $symptom)
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $symptom)) }} Frequency</label>
                            <select name="{{ $symptom }}_frequency" class="form-select">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="occasionally">Occasionally</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Intensity (1–10)</label>
                            <input type="range" name="{{ $symptom }}" min="1" max="10" class="form-range">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Comments</label>
                            <input type="text" name="{{ $symptom }}_comments" class="form-control">
                        </div>
                    </div>
                @endforeach

                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Hallucinations</label>
                        <select name="hallucinations" class="form-select">
                            <option value="">None</option>
                            <option value="auditory">Auditory</option>
                            <option value="visual">Visual</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Delusions</label>
                        <select name="delusions" class="form-select">
                            <option value="">None</option>
                            <option value="paranoid">Paranoid</option>
                            <option value="grandiose">Grandiose</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <h6 class="mt-4">2. Functional Status</h6>
                @foreach(['self_care', 'work_school', 'social_interactions', 'daily_activities'] as $domain)
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $domain)) }}</label>
                            <select name="{{ $domain }}" class="form-select">
                                <option value="Independent">Independent</option>
                                <option value="Needs support">Needs support</option>
                                <option value="Dependent">Dependent</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Comments</label>
                            <input type="text" name="{{ $domain }}_comments" class="form-control">
                        </div>
                    </div>
                @endforeach

                <h6 class="mt-4">3. Cognitive & Emotional Functioning</h6>
                @foreach(['attention', 'memory', 'decision_making', 'emotional_regulation', 'insight'] as $aspect)
                    <div class="mb-2">
                        <label class="form-label">{{ ucfirst(str_replace('_', ' ', $aspect)) }}</label>
                        <input type="text" name="{{ $aspect }}" class="form-control">
                    </div>
                @endforeach

                <h6 class="mt-4">4. Behavioral Observations</h6>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Medication Adherence</label>
                        <select name="medication_adherence" class="form-select">
                            <option value="yes">Yes</option>
                            <option value="partial">Partial</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Therapy Engagement</label>
                        <select name="therapy_engagement" class="form-select">
                            <option value="attending">Attending</option>
                            <option value="partial">Partial</option>
                            <option value="not_attending">Not attending</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Risk Behaviors</label>
                        <select name="risk_behaviors" class="form-select">
                            <option value="">None</option>
                            <option value="self-harm">Self-harm</option>
                            <option value="aggression">Aggression</option>
                            <option value="substance use">Substance use</option>
                        </select>
                    </div>
                </div>

                <h6 class="mt-4">5. Physical Health</h6>
                <div class="row mb-2">
                    <div class="col-md-3"><input type="text" name="weight" class="form-control" placeholder="Weight (kg)"></div>
                    <div class="col-md-3"><input type="text" name="vital_signs" class="form-control" placeholder="Vital Signs"></div>
                    <div class="col-md-3"><input type="text" name="medication_side_effects" class="form-control" placeholder="Side Effects"></div>
                    <div class="col-md-3"><input type="text" name="general_health" class="form-control" placeholder="General Health"></div>
                </div>

                <h6 class="mt-4">6. Social Support & Environment</h6>
                @foreach(['family_support', 'peer_support', 'housing_stability', 'access_to_services'] as $support)
                    <div class="mb-2">
                        <label class="form-label">{{ ucfirst(str_replace('_', ' ', $support)) }}</label>
                        <input type="text" name="{{ $support }}" class="form-control">
                    </div>
                @endforeach

                <h6 class="mt-4">7. Risk Assessment</h6>
                <div class="row mb-2">
                    @foreach(['suicide_risk', 'homicide_risk', 'self_neglect_risk', 'vulnerability_risk'] as $risk)
                        <div class="col-md-3">
                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $risk)) }}</label>
                            <select name="{{ $risk }}" class="form-select">
                                <option value="1">Low</option>
                                <option value="2">Moderate</option>
                                <option value="3">High</option>
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
                            <input type="number" name="treatment_goals[{{ $i }}][baseline]" class="form-control" placeholder="Baseline">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="treatment_goals[{{ $i }}][current]" class="form-control" placeholder="Current">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="treatment_goals[{{ $i }}][notes]" class="form-control" placeholder="Notes">
                        </div>
                    </div>
                @endfor

                <h6 class="mt-4">Clinician Summary / Recommendations</h6>
                <textarea name="notes" class="form-control mb-3" rows="4" placeholder="Summary, observations, and recommendations..."></textarea>

                <div class="mb-3">
                    <label class="form-label">Next Review Date</label>
                    <input type="date" name="next_review_date" class="form-control">
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Submit Report</button>
                </div>
            </form>
        </div>

        <!-- View Reports Tab -->
        <div class="tab-pane fade" id="view" role="tabpanel">
            <form method="GET" action="{{ route('progress-reports.index') }}" class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Select Patient</label>
                    <select name="patient_id" class="form-select" required>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 align-self-end">
                    <button type="submit" class="btn btn-outline-primary">View Progress</button>
                </div>
            </form>

            @if(isset($selectedPatient))
                <h5 class="mb-3">Progress Reports for {{ $selectedPatient->first_name }} {{ $selectedPatient->last_name }}</h5>
                @forelse($selectedPatient->progressReports as $report)
                    <div class="card mb-3">
                        <div class="card-header">
                            {{ $report->created_at->format('Y-m-d') }} — {{ $report->reporter->name }}
                        </div>
                        <div class="card-body">
                            <p><strong>Depressed Mood:</strong>
                                <span class="badge bg-{{ $report->depressed_mood >= 7 ? 'danger' : ($report->depressed_mood >= 4 ? 'warning' : 'success') }}">
                                    {{ $report->depressed_mood }}/10
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
                            <p><strong>Notes:</strong> {{ $report->notes ?? '—' }}</p>
                            @if(is_array($report->treatment_goals))
                                <hr>
                                <h6>Treatment Goals</h6>
                                @foreach($report->treatment_goals as $goal)
                                    <p>
                                        <strong>{{ $goal['goal'] ?? '—' }}</strong><br>
                                        Baseline: {{ $goal['baseline'] ?? '—' }} | Current: {{ $goal['current'] ?? '—' }}<br>
                                        <em>{{ $goal['notes'] ?? '' }}</em>
                                    </p>
                                @endforeach
                            @endif
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
    });
</script>

@endsection

{{-- @push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('#reportTabs button[data-bs-toggle="tab"]');

        // Restore last active tab from localStorage
        const lastTab = localStorage.getItem('activeTab');
        if (lastTab) {
            const trigger = document.querySelector(`#reportTabs button[data-bs-target="${lastTab}"]`);
            if (trigger) {
                new bootstrap.Tab(trigger).show();
            }
        }

        // Save active tab on click
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function (event) {
                const target = event.target.getAttribute('data-bs-target');
                localStorage.setItem('activeTab', target);
            });
        });
    });
</script>
@endpush --}}
