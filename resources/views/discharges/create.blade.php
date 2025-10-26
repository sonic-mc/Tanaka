@extends('layouts.app')

@section('title', 'Discharge Admission #'.$admission->id)

@section('content')
<div class="container">
    <h1 class="mb-3">Discharge Patient</h1>

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
                <dd class="col-sm-9">#{{ $admission->id }}</dd>

                <dt class="col-sm-3">Patient</dt>
                <dd class="col-sm-9">{{ $admission->patient->name ?? ('Patient #'.$admission->patient_id) }}</dd>

                <dt class="col-sm-3">Admission Date</dt>
                <dd class="col-sm-9">{{ $admission->admission_date?->format('Y-m-d') }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9"><span class="badge bg-primary text-uppercase">{{ $admission->status }}</span></dd>

                @if(!empty($admission->room_number))
                <dt class="col-sm-3">Room</dt>
                <dd class="col-sm-9">{{ $admission->room_number }}</dd>
                @endif
            </dl>
        </div>
    </div>

    <form method="POST" action="{{ route('admissions.discharge.store', $admission) }}">
        @csrf

        <div class="card">
            <div class="card-header">Discharge Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Discharge Date</label>
                    <input type="date" name="discharge_date" class="form-control" value="{{ old('discharge_date', now()->format('Y-m-d')) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Discharge Notes</label>
                    <textarea name="discharge_notes" rows="4" class="form-control" placeholder="Summary, instructions, etc.">{{ old('discharge_notes') }}</textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Follow-up Plan</label>
                        <input type="text" name="follow_up_plan" class="form-control" value="{{ old('follow_up_plan') }}" placeholder="E.g., Review in 2 weeks">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Referral Facility</label>
                        <input type="text" name="referral_facility" class="form-control" value="{{ old('referral_facility') }}" placeholder="E.g., Community clinic">
                    </div>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="requires_follow_up" value="1" id="requires_follow_up" {{ old('requires_follow_up') ? 'checked' : '' }}>
                    <label class="form-check-label" for="requires_follow_up">
                        Requires follow-up
                    </label>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary">Discharge Patient</button>
            </div>
        </div>
    </form>
</div>
@endsection
