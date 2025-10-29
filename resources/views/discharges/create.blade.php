@extends('layouts.app')

@section('title', 'Discharge Patient')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">Discharge Patient</h3>

    <div class="card mb-3">
        <div class="card-body">
            <p class="mb-1"><strong>Patient:</strong> {{ $admission->patient->first_name ?? '' }} {{ $admission->patient->last_name ?? '' }}</p>
            <p class="mb-1"><strong>Admission Date:</strong> {{ optional($admission->admission_date)->format('Y-m-d') }}</p>
            <p class="mb-1"><strong>Status:</strong> {{ ucfirst($admission->status) }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('discharges.store', $admission) }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Discharge Date</label>
            <input type="date" name="discharge_date" class="form-control" required
                   value="{{ old('discharge_date', now()->format('Y-m-d')) }}">
            @error('discharge_date') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Discharge Notes</label>
            <textarea name="discharge_notes" class="form-control" rows="3">{{ old('discharge_notes') }}</textarea>
            @error('discharge_notes') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Follow-up Plan</label>
            <input type="text" name="follow_up_plan" class="form-control" value="{{ old('follow_up_plan') }}">
            @error('follow_up_plan') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Referral Facility</label>
            <input type="text" name="referral_facility" class="form-control" value="{{ old('referral_facility') }}">
            @error('referral_facility') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="1" id="requires_follow_up" name="requires_follow_up"
                   {{ old('requires_follow_up') ? 'checked' : '' }}>
            <label class="form-check-label" for="requires_follow_up">
                Requires follow-up
            </label>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary">Discharge Patient</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection