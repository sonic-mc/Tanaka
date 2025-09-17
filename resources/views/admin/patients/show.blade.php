@extends('layouts.app')

@section('header')
    Patient Profile: {{ $patient->first_name }} {{ $patient->last_name }}
@endsection

@section('content')
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general">General Info</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#evaluations">Evaluations</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#progress">Progress Reports</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#billing">Billing</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="general">
        <p><strong>Patient Code:</strong> {{ $patient->patient_code }}</p>
        <p><strong>Gender:</strong> {{ ucfirst($patient->gender) }}</p>
        <p><strong>Admission Date:</strong> {{ $patient->admission_date }}</p>
        <p><strong>Care Level:</strong> {{ $patient->careLevel->name ?? '—' }}</p>
    </div>

 
</div>
@endsection
