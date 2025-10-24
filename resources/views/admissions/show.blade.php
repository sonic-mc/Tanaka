@extends('layouts.app')

@section('title', 'Admission Details')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Admission Details</h5>
            <div>
                <a href="{{ route('admissions.edit', $admission) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <a href="{{ route('admissions.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="card-body">
            <h6>Patient</h6>
            <p class="mb-1"><strong>Name:</strong> {{ $admission->patient->first_name ?? '' }} {{ $admission->patient->last_name ?? '' }}</p>
            <p class="mb-1"><strong>Patient Code:</strong> {{ $admission->patient->patient_code ?? '' }}</p>
            <p class="mb-1"><strong>Next of Kin:</strong> {{ $admission->patient->next_of_kin_name ?? 'N/A' }} ({{ $admission->patient->next_of_kin_email ?? 'No email' }})</p>

            <hr>

            <h6>Admission</h6>
            <p class="mb-1"><strong>Admission Date:</strong> {{ optional($admission->admission_date)->format('Y-m-d') }}</p>
            <p class="mb-1"><strong>Room:</strong> {{ $admission->room_number ?? '—' }}</p>
            <p class="mb-1"><strong>Care level id:</strong> {{ $admission->care_level_id ?? '—' }}</p>
            <p class="mb-1"><strong>Reason:</strong> {!! nl2br(e($admission->admission_reason ?? '—')) !!}</p>
            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-{{ $admission->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($admission->status) }}</span></p>

            @if($admission->evaluation)
                <hr>
                <h6>Referencing Evaluation</h6>
                <p class="mb-1"><strong>Evaluation date:</strong> {{ optional($admission->evaluation->evaluation_date)->format('Y-m-d') }}</p>
                <p class="mb-1"><strong>Decision:</strong> {{ ucfirst($admission->evaluation->decision) }}</p>
                <p class="mb-1"><strong>Notes:</strong> {!! nl2br(e($admission->evaluation->admission_trigger_notes ?? '—')) !!}</p>
            @endif
        </div>
    </div>
</div>
@endsection
