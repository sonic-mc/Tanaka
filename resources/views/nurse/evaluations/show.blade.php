@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Evaluation Details</h2>

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-person-badge-fill me-1"></i> Patient Information
        </div>
        <div class="card-body">
            <p><strong>Patient Name:</strong> {{ $evaluation->patient->first_name }} {{ $evaluation->patient->last_name }}</p>
            <p><strong>Patient Code:</strong> {{ $evaluation->patient->patient_code }}</p>
            <p><strong>Gender:</strong> {{ ucfirst($evaluation->patient->gender) }}</p>
            <p><strong>Admission Date:</strong> {{ $evaluation->patient->admission_date->format('d M Y') }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            <i class="bi bi-person-circle me-1"></i> Evaluator
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $evaluation->evaluator->name }}</p>
            <p><strong>Email:</strong> {{ $evaluation->evaluator->email }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-warning text-dark">
            <i class="bi bi-journal-text me-1"></i> Evaluation Details
        </div>
        <div class="card-body">
            <p><strong>Notes:</strong> {{ $evaluation->notes ?? 'N/A' }}</p>
            <p><strong>Risk Level:</strong> {{ ucfirst($evaluation->risk_level ?? 'N/A') }}</p>

            @if($evaluation->scores)
                <p><strong>Scores:</strong></p>
                <pre>{{ json_encode($evaluation->scores, JSON_PRETTY_PRINT) }}</pre>
            @else
                <p><strong>Scores:</strong> N/A</p>
            @endif

            <p><strong>Created At:</strong> {{ $evaluation->created_at->format('d M Y H:i') }}</p>
            <p><strong>Last Updated:</strong> {{ $evaluation->updated_at->format('d M Y H:i') }}</p>
        </div>
    </div>

    <a href="{{ route('evaluations.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle me-1"></i> Back to Evaluations
    </a>
</div>
@endsection
