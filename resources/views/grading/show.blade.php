@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Grading Preview</h3>
        <div class="text-muted">Evaluation-based grading with rationale</div>
    </div>
    <div>
        <form class="d-inline" action="{{ route('grading.recalculate', $evaluation) }}" method="POST" onsubmit="return confirm('Recalculate and save grading now?')">
            @csrf
            <button class="btn btn-outline-secondary">Recalculate & Save</button>
        </form>
        <a href="{{ route('grading.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-2"><strong>Patient:</strong> {{ $evaluation->patient?->first_name }} {{ $evaluation->patient?->last_name }} ({{ $evaluation->patient?->patient_code }})</div>
                <div class="mb-2"><strong>Date:</strong> {{ optional($evaluation->evaluation_date)->format('Y-m-d') }}</div>
                <div class="mb-2"><strong>Decision:</strong> {{ ucfirst($evaluation->decision) }}</div>
                <div class="mb-2"><strong>Requires Admission:</strong> {{ $evaluation->requires_admission ? 'Yes' : 'No' }}</div>
            </div>
            <div class="col-md-6">
                @php
                    $sev = strtolower($preview['severity_level'] ?? 'mild');
                    $sevClass = match($sev) {
                        'critical' => 'bg-dark',
                        'severe' => 'bg-danger',
                        'moderate' => 'bg-warning text-dark',
                        default => 'bg-success',
                    };
                    $risk = strtolower($preview['risk_level'] ?? 'low');
                    $riskClass = match($risk) {
                        'high' => 'bg-danger',
                        'medium' => 'bg-warning text-dark',
                        default => 'bg-success',
                    };
                @endphp
                <div class="mb-2"><strong>Severity:</strong> <span class="badge {{ $sevClass }}">{{ ucfirst($sev) }}</span></div>
                <div class="mb-2"><strong>Risk:</strong> <span class="badge {{ $riskClass }}">{{ ucfirst($risk) }}</span></div>
                <div class="mb-2"><strong>Priority:</strong> <span class="badge bg-primary">{{ $preview['priority_score'] ?? '—' }}</span></div>
            </div>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-md-6">
                <strong>Diagnosis</strong>
                <div class="text-muted">{{ $evaluation->diagnosis ?: '—' }}</div>
            </div>
            <div class="col-md-6">
                <strong>Recommendations</strong>
                <div class="text-muted">{{ $evaluation->recommendations ?: '—' }}</div>
            </div>
            <div class="col-md-12">
                <strong>Admission Trigger Notes</strong>
                <div class="text-muted">{{ $evaluation->admission_trigger_notes ?: '—' }}</div>
            </div>
        </div>
        <hr>
        <div>
            <strong>Rationale</strong>
            <ul class="mt-2">
                @forelse($preview['rationale'] ?? [] as $r)
                    <li>{{ $r }}</li>
                @empty
                    <li class="text-muted">No specific rule fired; default grading applied.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
