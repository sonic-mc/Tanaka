@extends('layouts.app')

@section('title', 'Edit Therapy Session')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Therapy Session #{{ $therapySession->id }}</h5>
            <a href="{{ route('therapy-sessions.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>There were some problems with your input:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('therapy-sessions.update', $therapySession->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Relationships -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="patient_id" class="form-label">Patient</label>
                        <select name="patient_id" id="patient_id" class="form-select" required>
                            @foreach($patients as $patient)
                                @php
                                    $pf = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
                                @endphp
                                <option value="{{ $patient->id }}"
                                    @selected(old('patient_id', $therapySession->patient_id) == $patient->id)>
                                    {{ $pf !== '' ? $pf : ($patient->name ?? 'Unnamed') }}
                                    @if(!empty($patient->patient_code)) ({{ $patient->patient_code }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="clinician_id" class="form-label">Clinician</label>
                        @if($canAssignClinician)
                            <select name="clinician_id" id="clinician_id" class="form-select">
                                <option value="">Select Clinician (Psychiatrist/Nurse)</option>
                                @foreach($clinicians as $clinician)
                                    <option value="{{ $clinician->id }}"
                                        @selected(old('clinician_id', $therapySession->clinician_id) == $clinician->id)>
                                        {{ $clinician->name }} — {{ ucfirst($clinician->role) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only Admin can change clinician assignment.</small>
                        @else
                            <input type="text" class="form-control"
                                   value="{{ $therapySession->clinician?->name ?? auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})" disabled>
                            <small class="text-muted">You are assigned as the clinician.</small>
                        @endif
                    </div>
                </div>

                <!-- Session Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="session_start" class="form-label">Session Start</label>
                        <input type="datetime-local" name="session_start" id="session_start" class="form-control"
                               value="{{ old('session_start', optional($therapySession->session_start)->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="session_end" class="form-label">Session End</label>
                        <input type="datetime-local" name="session_end" id="session_end" class="form-control"
                               value="{{ old('session_end', optional($therapySession->session_end)->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="session_type" class="form-label">Type</label>
                        <select name="session_type" id="session_type" class="form-select" required>
                            <option value="individual" @selected(old('session_type', $therapySession->session_type)=='individual')>Individual</option>
                            <option value="group" @selected(old('session_type', $therapySession->session_type)=='group')>Group</option>
                            <option value="family" @selected(old('session_type', $therapySession->session_type)=='family')>Family</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="mode" class="form-label">Mode</label>
                        <select name="mode" id="mode" class="form-select" required>
                            <option value="in-person" @selected(old('mode', $therapySession->mode)=='in-person')>In-Person</option>
                            <option value="online" @selected(old('mode', $therapySession->mode)=='online')>Online</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="session_number" class="form-label">Session Number</label>
                        <input type="number" name="session_number" id="session_number" class="form-control" min="1"
                               value="{{ old('session_number', $therapySession->session_number) }}">
                    </div>
                </div>

                <!-- Clinical Content -->
                <div class="mb-3">
                    <label for="presenting_issues" class="form-label">Presenting Issues</label>
                    <textarea name="presenting_issues" id="presenting_issues" class="form-control" rows="3">{{ old('presenting_issues', $therapySession->presenting_issues) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="mental_status_exam" class="form-label">Mental Status Exam</label>
                    <textarea name="mental_status_exam" id="mental_status_exam" class="form-control" rows="3">{{ old('mental_status_exam', $therapySession->mental_status_exam) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="interventions" class="form-label">Interventions</label>
                    <textarea name="interventions" id="interventions" class="form-control" rows="3">{{ old('interventions', $therapySession->interventions) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="observations" class="form-label">Observations</label>
                    <textarea name="observations" id="observations" class="form-control" rows="3">{{ old('observations', $therapySession->observations) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="plan" class="form-label">Plan</label>
                    <textarea name="plan" id="plan" class="form-control" rows="3">{{ old('plan', $therapySession->plan) }}</textarea>
                </div>

                <!-- Goals Progress -->
                <div class="mb-3">
                    <label for="goals_progress" class="form-label">Goals Progress (JSON)</label>
                    <textarea name="goals_progress" id="goals_progress" class="form-control" rows="5">@php
                        echo old('goals_progress', $therapySession->goals_progress ? json_encode($therapySession->goals_progress, JSON_PRETTY_PRINT) : '');
                    @endphp</textarea>
                    <small class="text-muted">Enter valid JSON, e.g. [{"goal":"Improve sleep","progress":"50%"}]</small>
                </div>

                <!-- Administrative -->
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="Scheduled" @selected(old('status', $therapySession->status)=='Scheduled')>Scheduled</option>
                        <option value="Completed" @selected(old('status', $therapySession->status)=='Completed')>Completed</option>
                        <option value="Canceled" @selected(old('status', $therapySession->status)=='Canceled')>Canceled</option>
                    </select>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Update Session</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
