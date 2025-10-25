@extends('layouts.app')

@section('content')
<h3>Evaluation Details</h3>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-2"><strong>Patient:</strong> {{ $evaluation->patient?->first_name }} {{ $evaluation->patient?->last_name }} ({{ $evaluation->patient?->patient_code }})</div>
                <div class="mb-2"><strong>Date:</strong> {{ optional($evaluation->evaluation_date)->format('Y-m-d') }}</div>
                <div class="mb-2"><strong>Type:</strong> {{ ucfirst($evaluation->evaluation_type) }}</div>
                <div class="mb-2"><strong>Decision:</strong> {{ ucfirst($evaluation->decision) }}</div>
                <div class="mb-2"><strong>Requires Admission:</strong> {{ $evaluation->requires_admission ? 'Yes' : 'No' }}</div>
                @if($evaluation->requires_admission)
                    <div class="mb-2"><strong>Admission Notes:</strong> {{ $evaluation->admission_trigger_notes }}</div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="mb-2"><strong>Psychiatrist:</strong> {{ $evaluation->psychiatrist?->name ?? '—' }}</div>
                <div class="mb-2"><strong>Decision Finalized At:</strong> {{ optional($evaluation->decision_made_at)->format('Y-m-d H:i') }}</div>
                <div class="mb-2"><strong>Status:</strong> {{ $evaluation->deleted_at ? 'Archived' : 'Active' }}</div>
                <div class="mb-2"><strong>Created:</strong> {{ $evaluation->created_at?->format('Y-m-d H:i') }} by {{ $evaluation->creator?->name ?? $evaluation->created_by }}</div>
                <div class="mb-2"><strong>Last Modified:</strong> {{ $evaluation->updated_at?->format('Y-m-d H:i') }} by {{ $evaluation->lastModifier?->name ?? $evaluation->last_modified_by }}</div>
            </div>
        </div>

        <hr>

        <div class="row g-3">
            <div class="col-md-4">
                <strong>Severity Level:</strong>
                @php
                    $sev = strtolower($evaluation->severity_level ?? 'mild');
                    $sevClass = match($sev) {
                        'critical' => 'bg-dark',
                        'severe' => 'bg-danger',
                        'moderate' => 'bg-warning text-dark',
                        default => 'bg-success',
                    };
                @endphp
                <span class="badge {{ $sevClass }}">{{ ucfirst($sev) }}</span>
            </div>
            <div class="col-md-4">
                <strong>Risk Level:</strong>
                @php
                    $risk = strtolower($evaluation->risk_level ?? 'low');
                    $riskClass = match($risk) {
                        'high' => 'bg-danger',
                        'medium' => 'bg-warning text-dark',
                        default => 'bg-success',
                    };
                @endphp
                <span class="badge {{ $riskClass }}">{{ ucfirst($risk) }}</span>
            </div>
            <div class="col-md-4">
                <strong>Priority Score:</strong>
                <span class="badge bg-primary">{{ $evaluation->priority_score !== null ? $evaluation->priority_score : '—' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Presenting Complaints</div>
            <div class="card-body">
                <p class="mb-0">{{ $evaluation->presenting_complaints ?: '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Clinical Observations</div>
            <div class="card-body">
                <p class="mb-0">{{ $evaluation->clinical_observations ?: '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mt-3">
            <div class="card-header">Diagnosis</div>
            <div class="card-body">
                <p class="mb-0">{{ $evaluation->diagnosis ?: '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mt-3">
            <div class="card-header">Recommendations</div>
            <div class="card-body">
                <p class="mb-0">{{ $evaluation->recommendations ?: '—' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-2">
    @if(!$evaluation->deleted_at)
        <a href="{{ route('evaluations.edit', $evaluation->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('evaluations.destroy', $evaluation->id) }}" method="POST" onsubmit="return confirm('Archive this evaluation?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger">Archive</button>
        </form>
    @else
        <form action="{{ route('evaluations.restore', $evaluation->id) }}" method="POST" onsubmit="return confirm('Restore this evaluation?')">
            @csrf
            <button class="btn btn-secondary">Restore</button>
        </form>
        <form action="{{ route('evaluations.force-delete', $evaluation->id) }}" method="POST" onsubmit="return confirm('Permanently delete this evaluation? This cannot be undone.')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger">Delete Permanently</button>
        </form>
    @endif
    <a href="{{ route('evaluations.index') }}" class="btn btn-outline-secondary">Back</a>
</div>
@endsection
