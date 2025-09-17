@extends('layouts.app')

@section('header')
    Patient: {{ $patient->first_name }} {{ $patient->last_name }}
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        Patient Information
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Patient Code:</strong> {{ $patient->patient_code }}
            </div>
            <div class="col-md-6">
                <strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($patient->status) }}</span>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Gender:</strong> {{ ucfirst($patient->gender) }}
            </div>
            <div class="col-md-6">
                <strong>Date of Birth:</strong> {{ $patient->dob ? $patient->dob->format('d M Y') : '—' }}
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Contact Number:</strong> {{ $patient->contact_number ?? '—' }}
            </div>
            <div class="col-md-6">
                <strong>Room Number:</strong> {{ $patient->room_number ?? '—' }}
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Admission Date:</strong> {{ $patient->admission_date->format('d M Y') }}
            </div>
            <div class="col-md-6">
                <strong>Admitted By:</strong> {{ $patient->admittedBy->name ?? '—' }}
            </div>
        </div>

        <div class="mb-3">
            <strong>Admission Reason:</strong>
            <p>{{ $patient->admission_reason ?? '—' }}</p>
        </div>

        <div class="mb-3">
            <strong>Care Level:</strong> {{ $patient->careLevel->name ?? '—' }}
        </div>

        <div class="mb-3 text-muted">
            <small>Created: {{ $patient->created_at->format('d M Y H:i') }}</small><br>
            <small>Last Updated: {{ $patient->updated_at->format('d M Y H:i') }}</small>
        </div>
    </div>
</div>
@endsection
