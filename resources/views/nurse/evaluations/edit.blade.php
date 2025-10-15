@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Evaluation</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('evaluations.update', $evaluation->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Patient Selection -->
        <div class="mb-3">
            <label for="patient_id" class="form-label">
                <i class="bi bi-person-badge-fill me-1"></i> Patient
            </label>
            <select id="patient_id" name="patient_id" class="form-select" required>
                @foreach($patients as $patient)
                    <option value="{{ $patient->id }}"
                        {{ $evaluation->patient_id == $patient->id ? 'selected' : '' }}>
                        {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Notes -->
        <div class="mb-3">
            <label for="notes" class="form-label">
                <i class="bi bi-journal-text me-1"></i> Notes
            </label>
            <textarea id="notes" name="notes" class="form-control" rows="4">{{ old('notes', $evaluation->notes) }}</textarea>
        </div>

        <!-- Risk Level -->
        <div class="mb-3">
            <label for="risk_level" class="form-label">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Risk Level
            </label>
            <select id="risk_level" name="risk_level" class="form-select">
                @foreach(['mild', 'moderate', 'severe'] as $level)
                    <option value="{{ $level }}" {{ $evaluation->risk_level === $level ? 'selected' : '' }}>
                        {{ ucfirst($level) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Scores (JSON, optional) -->
        <div class="mb-3">
            <label for="scores" class="form-label">
                <i class="bi bi-bar-chart-fill me-1"></i> Scores (JSON)
            </label>
            <textarea id="scores" name="scores" class="form-control" rows="3">{{ old('scores', json_encode($evaluation->scores, JSON_PRETTY_PRINT)) }}</textarea>
            <small class="text-muted">Optional: Enter scores in JSON format.</small>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Update Evaluation
        </button>
        <a href="{{ route('evaluations.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection
