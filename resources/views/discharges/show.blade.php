@extends('layouts.app')

@section('title', 'Discharge #'.$discharge->id)

@section('content')
<div class="container">
    <h1 class="mb-3">Discharge Details</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Patient & Admission</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Patient</dt>
                <dd class="col-sm-9">{{ $discharge->patient->name ?? ('Patient #'.$discharge->patient_id) }}</dd>

                <dt class="col-sm-3">Admission</dt>
                <dd class="col-sm-9">#{{ $discharge->admission_id }} ({{ $discharge->admission?->admission_date?->format('Y-m-d') }})</dd>

                <dt class="col-sm-3">Admission Status</dt>
                <dd class="col-sm-9"><span class="badge bg-secondary">{{ $discharge->admission?->status ?? 'N/A' }}</span></dd>
            </dl>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Discharge</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Discharge Date</dt>
                <dd class="col-sm-9">{{ $discharge->discharge_date?->format('Y-m-d') }}</dd>

                <dt class="col-sm-3">Discharged By</dt>
                <dd class="col-sm-9">{{ $discharge->dischargedBy->name ?? 'User #'.$discharge->discharged_by }}</dd>

                <dt class="col-sm-3">Requires Follow-up</dt>
                <dd class="col-sm-9">{{ $discharge->requires_follow_up ? 'Yes' : 'No' }}</dd>

                @if($discharge->follow_up_plan)
                <dt class="col-sm-3">Follow-up Plan</dt>
                <dd class="col-sm-9">{{ $discharge->follow_up_plan }}</dd>
                @endif

                @if($discharge->referral_facility)
                <dt class="col-sm-3">Referral Facility</dt>
                <dd class="col-sm-9">{{ $discharge->referral_facility }}</dd>
                @endif

                @if($discharge->discharge_notes)
                <dt class="col-sm-3">Notes</dt>
                <dd class="col-sm-9"><pre class="mb-0">{{ $discharge->discharge_notes }}</pre></dd>
                @endif
            </dl>
        </div>
        <div class="card-footer d-flex gap-2">
            <a class="btn btn-outline-primary" href="{{ route('discharges.edit', $discharge) }}">Edit</a>
            <form method="POST" action="{{ route('discharges.destroy', $discharge) }}" onsubmit="return confirm('Delete this discharge record?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">Delete</button>
            </form>
            <a class="btn btn-outline-secondary ms-auto" href="{{ route('discharges.index') }}">Back to list</a>
        </div>
    </div>
</div>
@endsection
