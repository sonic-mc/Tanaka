@extends('layouts.app')

@section('title', 'Edit Discharge #'.$discharge->id)

@section('content')
<div class="container">
    <h1 class="mb-3">Edit Discharge</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Admission Summary</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Admission ID</dt>
                <dd class="col-sm-9">#{{ $discharge->admission_id }}</dd>

                <dt class="col-sm-3">Patient</dt>
                <dd class="col-sm-9">{{ $discharge->patient->name ?? ('Patient #'.$discharge->patient_id) }}</dd>

                <dt class="col-sm-3">Admission Date</dt>
                <dd class="col-sm-9">{{ $discharge->admission?->admission_date?->format('Y-m-d') }}</dd>
            </dl>
        </div>
    </div>

    <form method="POST" action="{{ route('discharges.update', $discharge) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">Discharge Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Discharge Date</label>
                    <input type="date" name="discharge_date" class="form-control" value="{{ old('discharge_date', $discharge->discharge_date?->format('Y-m-d')) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Discharge Notes</label>
                    <textarea name="discharge_notes" rows="4" class="form-control">{{ old('discharge_notes', $discharge->discharge_notes) }}</textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Follow-up Plan</label>
                        <input type="text" name="follow_up_plan" class="form-control" value="{{ old('follow_up_plan', $discharge->follow_up_plan) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Referral Facility</label>
                        <input type="text" name="referral_facility" class="form-control" value="{{ old('referral_facility', $discharge->referral_facility) }}">
                    </div>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="requires_follow_up" value="1" id="requires_follow_up" {{ old('requires_follow_up', $discharge->requires_follow_up) ? 'checked' : '' }}>
                    <label class="form-check-label" for="requires_follow_up">
                        Requires follow-up
                    </label>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('discharges.show', $discharge) }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
</div>
@endsection
