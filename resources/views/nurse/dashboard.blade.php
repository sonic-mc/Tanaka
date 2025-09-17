@extends('layouts.app')

@section('header')
    Nurse Dashboard
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5>Assigned Patients</h5>
                <p>{{ $assignedPatients }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5>Pending Reports</h5>
                <p>{{ $pendingReports }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Upcoming Evaluations</h5>
                <p>{{ $upcomingEvaluations }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
