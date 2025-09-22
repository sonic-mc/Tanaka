@extends('layouts.app')

@section('header')
    Admin Dashboard
@endsection

@section('content')
<div class="row">
    {{-- Patients --}}
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Patients</h5>
                <p class="card-text">{{ $patientCount }}</p>
            </div>
        </div>
    </div>

    {{-- Staff --}}
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Active Staff</h5>
                <p class="card-text">{{ $staffCount }}</p>
            </div>
        </div>
    </div>

    {{-- Tasks --}}
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Pending Tasks</h5>
                <p class="card-text">{{ $pendingTasks }}</p>
            </div>
        </div>
    </div>

    {{-- Incidents --}}
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Critical Incidents</h5>
                <p class="card-text">{{ $criticalIncidents }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Therapy Sessions --}}
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Therapy Sessions</h5>
                <p class="card-text">{{ $therapySessionCount }}</p>
            </div>
        </div>
    </div>

    {{-- Evaluations --}}
    <div class="col-md-3">
        <div class="card text-white bg-secondary mb-3">
            <div class="card-body">
                <h5 class="card-title">Evaluations</h5>
                <p class="card-text">{{ $evaluationCount }}</p>
            </div>
        </div>
    </div>

    {{-- Progress Reports --}}
    <div class="col-md-3">
        <div class="card text-white bg-dark mb-3">
            <div class="card-body">
                <h5 class="card-title">Progress Reports</h5>
                <p class="card-text">{{ $progressReportCount }}</p>
            </div>
        </div>
    </div>

    {{-- Discharges --}}
    <div class="col-md-3">
        <div class="card text-white bg-teal mb-3">
            <div class="card-body">
                <h5 class="card-title">Discharges</h5>
                <p class="card-text">{{ $dischargeCount }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Billing Statements --}}
    <div class="col-md-3">
        <div class="card text-white bg-indigo mb-3">
            <div class="card-body">
                <h5 class="card-title">Billing Statements</h5>
                <p class="card-text">{{ $billingCount }}</p>
            </div>
        </div>
    </div>

    {{-- Payments --}}
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Payments</h5>
                <p class="card-text">{{ $paymentCount }}</p>
            </div>
        </div>
    </div>

    {{-- Notifications --}}
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Notifications</h5>
                <p class="card-text">{{ $notificationCount }}</p>
            </div>
        </div>
    </div>

    {{-- Audit Logs --}}
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Audit Logs</h5>
                <p class="card-text">{{ $auditLogCount }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Care Levels --}}
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Care Levels</h5>
                <p class="card-text">{{ $careLevelCount }}</p>
            </div>
        </div>
    </div>

    {{-- Backups --}}
    <div class="col-md-3">
        <div class="card text-white bg-secondary mb-3">
            <div class="card-body">
                <h5 class="card-title">System Backups</h5>
                <p class="card-text">{{ $backupCount }}</p>
            </div>
        </div>
    </div>

    {{-- Profiles --}}
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">User Profiles</h5>
                <p class="card-text">{{ $profileCount }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
