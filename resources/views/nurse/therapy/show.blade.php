@extends('layouts.app')

@section('title', 'View Therapy Session')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Therapy Session #{{ $therapySession->id }}</h5>
            <div>
                <a href="{{ route('therapy-sessions.edit', $therapySession->id) }}" class="btn btn-primary btn-sm">Edit</a>
                <a href="{{ route('therapy-sessions.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Patient:</strong><br>
                    @if($therapySession->patient)
                        @php
                            $pf = trim(($therapySession->patient->first_name ?? '') . ' ' . ($therapySession->patient->last_name ?? ''));
                        @endphp
                        {{ $pf !== '' ? $pf : ($therapySession->patient->name ?? 'Unnamed') }}
                        @if(!empty($therapySession->patient->patient_code)) ({{ $therapySession->patient->patient_code }}) @endif
                    @else
                        <em>Patient removed</em>
                    @endif
                </div>
                <div class="col-md-4">
                    <strong>Clinician:</strong><br>
                    {{ $therapySession->clinician?->name ?? '—' }}
                </div>
                <div class="col-md-4">
                    <strong>Status:</strong><br>
                    <span class="badge 
                        @if($therapySession->status == 'Completed') bg-success
                        @elseif($therapySession->status == 'Scheduled') bg-warning
                        @elseif($therapySession->status == 'Canceled') bg-danger
                        @else bg-secondary @endif">
                        {{ $therapySession->status }}
                    </span>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Session Start:</strong><br>
                    {{ optional($therapySession->session_start)->format('d M Y, H:i') }}
                </div>
                <div class="col-md-4">
                    <strong>Session End:</strong><br>
                    {{ $therapySession->session_end ? $therapySession->session_end->format('d M Y, H:i') : '-' }}
                </div>
                <div class="col-md-2">
                    <strong>Type:</strong><br>
                    {{ ucfirst($therapySession->session_type) }}
                </div>
                <div class="col-md-2">
                    <strong>Mode:</strong><br>
                    {{ ucfirst($therapySession->mode) }}
                </div>
            </div>

            @if($therapySession->session_number)
                <div class="mb-3">
                    <strong>Session Number:</strong><br>
                    {{ $therapySession->session_number }}
                </div>
            @endif

            @if($therapySession->presenting_issues)
                <div class="mb-3">
                    <strong>Presenting Issues</strong>
                    <p class="mb-0">{{ $therapySession->presenting_issues }}</p>
                </div>
            @endif

            @if($therapySession->mental_status_exam)
                <div class="mb-3">
                    <strong>Mental Status Exam</strong>
                    <p class="mb-0">{{ $therapySession->mental_status_exam }}</p>
                </div>
            @endif

            @if($therapySession->interventions)
                <div class="mb-3">
                    <strong>Interventions</strong>
                    <p class="mb-0">{{ $therapySession->interventions }}</p>
                </div>
            @endif

            @if($therapySession->observations)
                <div class="mb-3">
                    <strong>Observations</strong>
                    <p class="mb-0">{{ $therapySession->observations }}</p>
                </div>
            @endif

            @if($therapySession->plan)
                <div class="mb-3">
                    <strong>Plan</strong>
                    <p class="mb-0">{{ $therapySession->plan }}</p>
                </div>
            @endif

            @if(!empty($therapySession->goals_progress))
                <div class="mb-3">
                    <strong>Goals Progress</strong>
                    <pre class="bg-light p-3 rounded border mb-0" style="white-space: pre-wrap;">{{ json_encode($therapySession->goals_progress, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
