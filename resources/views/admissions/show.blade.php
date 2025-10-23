@extends('layouts.app')
@section('content')
<h3>Admission Details</h3>
<div class="mb-3">
    <strong>Patient:</strong> {{ $admission->patient->first_name }} {{ $admission->patient->last_name }}<br>
    <strong>Admission Date:</strong> {{ $admission->admission_date }}<br>
    <strong>Status:</strong> {{ ucfirst($admission->status) }}<br>
    <strong>Room:</strong> {{ $admission->room_number }}<br>
    <strong>Care Level ID:</strong> {{ $admission->care_level_id ?? 'N/A' }}<br>
</div>

<div class="mb-3">
    <strong>Admission Reason:</strong><br>
    <p>{{ $admission->admission_reason }}</p>
</div>

@if($admission->status === 'active')
    <a href="{{ route('evaluations.create', ['patient_id' => $admission->patient_id]) }}" class="btn btn-outline-primary">
        Re-Evaluate Patient
    </a>
@endif


<a href="{{ route('admissions.edit', $admission) }}" class="btn btn-warning">Edit</a>
<a href="{{ route('admissions.index') }}" class="btn btn-secondary">Back</a>
@endsection
