@extends('layouts.app')

@section('header')
    Dashboard
@endsection

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Patients</h5>
                <p class="card-text">{{ $patientCount }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Active Staff</h5>
                <p class="card-text">{{ $staffCount }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Pending Tasks</h5>
                <p class="card-text">{{ $pendingTasks }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Critical Incidents</h5>
                <p class="card-text">{{ $criticalIncidents }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
