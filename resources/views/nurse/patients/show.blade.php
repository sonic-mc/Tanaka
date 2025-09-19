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
    <!-- General Info -->
    <div class="tab-pane fade show active" id="general">
        <p><strong>Patient Code:</strong> {{ $patient->patient_code }}</p>
        <p><strong>Full Name:</strong> {{ $patient->first_name }} {{ $patient->last_name }}</p>
        <p><strong>Gender:</strong> {{ ucfirst($patient->gender) }}</p>
        <p><strong>Date of Birth:</strong> {{ $patient->dob ?? '—' }}</p>
        <p><strong>Contact Number:</strong> {{ $patient->contact_number ?? '—' }}</p>
        <p><strong>Admission Date:</strong> {{ $patient->admission_date }}</p>
        <p><strong>Admission Reason:</strong> {{ $patient->admission_reason ?? '—' }}</p>
        <p><strong>Admitted By:</strong> {{ $patient->admittedBy->name ?? '—' }}</p>
        <p><strong>Room Number:</strong> {{ $patient->room_number ?? '—' }}</p>
        <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($patient->status) }}</span></p>
        <p><strong>Care Level:</strong> {{ $patient->careLevel->name ?? '—' }}</p>
        <p><strong>Last Updated:</strong> {{ $patient->updated_at->format('Y-m-d H:i') }}</p>
    </div>

    <!-- Evaluations -->
    <div class="tab-pane fade" id="evaluations">
        @forelse($evaluations as $evaluation)
            <div class="border rounded p-3 mb-3">
                <p><strong>Evaluated By:</strong> {{ $evaluation->evaluator->name ?? '—' }}</p>
                <p><strong>Risk Level:</strong> {{ ucfirst($evaluation->risk_level) ?? '—' }}</p>
                <p><strong>Scores:</strong> {{ is_array($evaluation->scores) ? json_encode($evaluation->scores) : $evaluation->scores }}</p>
                <p><strong>Notes:</strong> {{ $evaluation->notes ?? '—' }}</p>
                <p class="text-muted"><small>Evaluated on {{ $evaluation->created_at->format('Y-m-d') }}</small></p>
            </div>
        @empty
            <p class="text-muted">No evaluations recorded.</p>
        @endforelse
    </div>

    <!-- Progress Reports -->
    <div class="tab-pane fade" id="progress">
        @forelse($progressReports as $report)
            <div class="border rounded p-3 mb-3">
                <p><strong>Reported By:</strong> {{ $report->reporter->name ?? '—' }}</p>
                <p><strong>Behavior:</strong> {{ $report->behavior ?? '—' }}</p>
                <p><strong>Medication Response:</strong> {{ $report->medication_response ?? '—' }}</p>
                <p><strong>Attendance:</strong> {{ $report->attendance ? 'Present' : 'Absent' }}</p>
                <p><strong>Notes:</strong> {{ $report->notes ?? '—' }}</p>
                <p class="text-muted"><small>Reported on {{ $report->created_at->format('Y-m-d') }}</small></p>
            </div>
        @empty
            <p class="text-muted">No progress reports available.</p>
        @endforelse
    </div>

    <!-- Billing -->
    <div class="tab-pane fade" id="billing">
        @if($billingStatement)
            <p><strong>Total Amount:</strong> ${{ number_format($billingStatement->total_amount, 2) }}</p>
            <p><strong>Outstanding Balance:</strong> ${{ number_format($billingStatement->outstanding_balance, 2) }}</p>
            <p><strong>Last Updated:</strong> {{ $billingStatement->last_updated->format('Y-m-d H:i') }}</p>
        @else
            <p class="text-muted">No billing statement found.</p>
        @endif
    </div>
</div>
@endsection
