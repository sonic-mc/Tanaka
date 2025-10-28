@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Evaluation #{{ $evaluation->id }}</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('evaluations.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('evaluations.edit', $evaluation->id) }}" class="btn btn-primary">Edit</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Patient</h5>
            <p>
                <strong>{{ $evaluation->patient?->patient_code }}</strong> —
                {{ $evaluation->patient?->full_name }}
            </p>

            <h5 class="mt-4">Evaluation Details</h5>
            <dl class="row">
                <dt class="col-sm-3">Date</dt>
                <dd class="col-sm-9">{{ $evaluation->evaluation_date?->format('Y-m-d') }}</dd>

                <dt class="col-sm-3">Type</dt>
                <dd class="col-sm-9">{{ ucfirst($evaluation->evaluation_type) }}</dd>

                <dt class="col-sm-3">Severity</dt>
                <dd class="col-sm-9">{{ ucfirst($evaluation->severity_level) }}</dd>

                <dt class="col-sm-3">Risk</dt>
                <dd class="col-sm-9">{{ ucfirst($evaluation->risk_level) }}</dd>

                <dt class="col-sm-3">Priority</dt>
                <dd class="col-sm-9">{{ $evaluation->priority_score ?? '—' }}</dd>

                <dt class="col-sm-3">Decision</dt>
                <dd class="col-sm-9">{{ ucfirst($evaluation->decision) }}</dd>

                <dt class="col-sm-3">Requires Admission</dt>
                <dd class="col-sm-9">{{ $evaluation->requires_admission ? 'Yes' : 'No' }}</dd>

                @if($evaluation->admission_trigger_notes)
                    <dt class="col-sm-3">Admission Notes</dt>
                    <dd class="col-sm-9">{{ $evaluation->admission_trigger_notes }}</dd>
                @endif

                <dt class="col-sm-3">Decision Time</dt>
                <dd class="col-sm-9">{{ $evaluation->decision_made_at?->format('Y-m-d H:i') }}</dd>
            </dl>

            <h5 class="mt-4">Clinical Notes</h5>
            <dl class="row">
                <dt class="col-sm-3">Presenting Complaints</dt>
                <dd class="col-sm-9">{{ $evaluation->presenting_complaints ?? '—' }}</dd>

                <dt class="col-sm-3">Clinical Observations</dt>
                <dd class="col-sm-9">{{ $evaluation->clinical_observations ?? '—' }}</dd>

                <dt class="col-sm-3">Diagnosis</dt>
                <dd class="col-sm-9">{{ $evaluation->diagnosis ?? '—' }}</dd>

                <dt class="col-sm-3">Recommendations</dt>
                <dd class="col-sm-9">{{ $evaluation->recommendations ?? '—' }}</dd>
            </dl>

            <h5 class="mt-4">Administrative</h5>
            <dl class="row">
                <dt class="col-sm-3">Psychiatrist</dt>
                <dd class="col-sm-9">{{ $evaluation->psychiatrist?->name ?? ('User #'.$evaluation->psychiatrist_id) }}</dd>

                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9">
                    {{ $evaluation->created_at?->format('Y-m-d H:i') }} by #{{ $evaluation->created_by }}
                </dd>

                <dt class="col-sm-3">Last Modified</dt>
                <dd class="col-sm-9">
                    {{ $evaluation->updated_at?->format('Y-m-d H:i') }} by #{{ $evaluation->last_modified_by }}
                </dd>
            </dl>
        </div>
    </div>
</div>
@endsection
