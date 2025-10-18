@extends('layouts.app')

@section('title', 'Therapy Sessions Management')

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
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

    {{-- Dashboard Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-dark shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Sessions</h5>
                    <h3>{{ $totalSessions ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Completed</h5>
                    <h3>{{ $completedSessions ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Scheduled</h5>
                    <h3>{{ $scheduledSessions ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Cancelled</h5>
                    <h3>{{ $cancelledSessions ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3" id="therapySessionTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-sessions-tab" data-bs-toggle="tab" data-bs-target="#all-sessions" type="button" role="tab">
                All Sessions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="create-session-tab" data-bs-toggle="tab" data-bs-target="#create-session" type="button" role="tab">
                Create Session
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-sessions-tab" data-bs-toggle="tab" data-bs-target="#completed-sessions" type="button" role="tab">
                Completed
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="scheduled-sessions-tab" data-bs-toggle="tab" data-bs-target="#scheduled-sessions" type="button" role="tab">
                Scheduled
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="therapySessionTabsContent">
        {{-- All Sessions --}}
        <div class="tab-pane fade show active" id="all-sessions" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>All Therapy Sessions</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Patient</th>
                                <th>Clinician</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Type</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th style="width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                            <tr>
                                <td>{{ $loop->iteration + ($sessions->currentPage()-1)*$sessions->perPage() }}</td>
                                <td>
                                    @if($session->patient)
                                        @php
                                            $pf = trim(($session->patient->first_name ?? '') . ' ' . ($session->patient->last_name ?? ''));
                                        @endphp
                                        {{ $pf !== '' ? $pf : ($session->patient->name ?? 'Unnamed') }}
                                        @if(!empty($session->patient->patient_code))
                                            ({{ $session->patient->patient_code }})
                                        @endif
                                    @else
                                        <em>Patient removed</em>
                                    @endif
                                </td>
                                <td>{{ $session->clinician?->name ?? '—' }}</td>
                                <td>{{ optional($session->session_start)->format('d M Y, H:i') }}</td>
                                <td>{{ $session->session_end ? $session->session_end->format('d M Y, H:i') : '-' }}</td>
                                <td>{{ ucfirst($session->session_type) }}</td>
                                <td>{{ ucfirst($session->mode) }}</td>
                                <td>
                                    <span class="badge 
                                        @if($session->status == 'Completed') bg-success
                                        @elseif($session->status == 'Scheduled') bg-warning
                                        @elseif($session->status == 'Canceled') bg-danger
                                        @else bg-secondary @endif">
                                        {{ $session->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('therapy-sessions.show', $session->id) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('therapy-sessions.edit', $session->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('therapy-sessions.destroy', $session->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this session?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No therapy sessions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-end">
                        {{ $sessions->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Create Session --}}
        <div class="tab-pane fade" id="create-session" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Create Therapy Session</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('therapy-sessions.store') }}" method="POST">
                        @csrf

                        <!-- Relationships -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">Patient</label>
                                <select name="patient_id" id="patient_id" class="form-select" required>
                                    <option value="">Select Patient</option>
                                    @foreach($patients as $patient)
                                        @php
                                            $pf = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
                                        @endphp
                                        <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
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
                                            <option value="{{ $clinician->id }}" @selected(old('clinician_id') == $clinician->id)>
                                                {{ $clinician->name }} — {{ ucfirst($clinician->role) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Only Admin can assign a clinician. If left blank, you will be assigned.</small>
                                @else
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})" disabled>
                                    <small class="text-muted">You will be assigned as the clinician.</small>
                                @endif
                            </div>
                        </div>

                        <!-- Session Info -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="session_start" class="form-label">Session Start</label>
                                <input type="datetime-local" name="session_start" id="session_start" class="form-control" value="{{ old('session_start') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="session_end" class="form-label">Session End</label>
                                <input type="datetime-local" name="session_end" id="session_end" class="form-control" value="{{ old('session_end') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="session_type" class="form-label">Type</label>
                                <select name="session_type" id="session_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="individual" @selected(old('session_type')=='individual')>Individual</option>
                                    <option value="group" @selected(old('session_type')=='group')>Group</option>
                                    <option value="family" @selected(old('session_type')=='family')>Family</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="mode" class="form-label">Mode</label>
                                <select name="mode" id="mode" class="form-select" required>
                                    <option value="">Select Mode</option>
                                    <option value="in-person" @selected(old('mode')=='in-person')>In-Person</option>
                                    <option value="online" @selected(old('mode')=='online')>Online</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="session_number" class="form-label">Session Number</label>
                                <input type="number" name="session_number" id="session_number" class="form-control" min="1" value="{{ old('session_number') }}">
                            </div>
                        </div>

                        <!-- Clinical Content -->
                        <div class="mb-3">
                            <label for="presenting_issues" class="form-label">Presenting Issues</label>
                            <textarea name="presenting_issues" id="presenting_issues" class="form-control" rows="3">{{ old('presenting_issues') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="mental_status_exam" class="form-label">Mental Status Exam</label>
                            <textarea name="mental_status_exam" id="mental_status_exam" class="form-control" rows="3">{{ old('mental_status_exam') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="interventions" class="form-label">Interventions</label>
                            <textarea name="interventions" id="interventions" class="form-control" rows="3">{{ old('interventions') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="observations" class="form-label">Observations</label>
                            <textarea name="observations" id="observations" class="form-control" rows="3">{{ old('observations') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="plan" class="form-label">Plan</label>
                            <textarea name="plan" id="plan" class="form-control" rows="3">{{ old('plan') }}</textarea>
                        </div>

                        <!-- Goals Progress -->
                        <div class="mb-3">
                            <label for="goals_progress" class="form-label">Goals Progress (JSON)</label>
                            <textarea name="goals_progress" id="goals_progress" class="form-control" rows="3" placeholder='[{"goal":"Improve sleep","progress":"50%"}]'>{{ old('goals_progress') }}</textarea>
                            <small class="text-muted">Enter valid JSON, e.g. [{"goal":"Improve sleep","progress":"50%"}]</small>
                        </div>

                        <!-- Administrative -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="Scheduled" @selected(old('status')=='Scheduled')>Scheduled</option>
                                <option value="Completed" @selected(old('status')=='Completed')>Completed</option>
                                <option value="Canceled" @selected(old('status')=='Canceled')>Canceled</option>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">Create Session</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Completed Sessions --}}
        <div class="tab-pane fade" id="completed-sessions" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header"><h5>Completed Sessions</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Patient</th>
                                <th>Clinician</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Type</th>
                                <th>Mode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1; @endphp
                            @forelse($sessions->where('status','Completed') as $session)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>
                                    @if($session->patient)
                                        @php
                                            $pf = trim(($session->patient->first_name ?? '') . ' ' . ($session->patient->last_name ?? ''));
                                        @endphp
                                        {{ $pf !== '' ? $pf : ($session->patient->name ?? 'Unnamed') }}
                                    @else
                                        <em>Patient removed</em>
                                    @endif
                                </td>
                                <td>{{ $session->clinician?->name ?? '—' }}</td>
                                <td>{{ optional($session->session_start)->format('d M Y, H:i') }}</td>
                                <td>{{ $session->session_end ? $session->session_end->format('d M Y, H:i') : '-' }}</td>
                                <td>{{ ucfirst($session->session_type) }}</td>
                                <td>{{ ucfirst($session->mode) }}</td>
                                <td>
                                    <a href="{{ route('therapy-sessions.show', $session->id) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No completed sessions found (on this page).</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="text-muted small">
                        Note: This tab filters the current page of results. Use the "All Sessions" tab and pagination to navigate.
                    </div>
                </div>
            </div>
        </div>

        {{-- Scheduled Sessions --}}
        <div class="tab-pane fade" id="scheduled-sessions" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header"><h5>Scheduled Sessions</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Patient</th>
                                <th>Clinician</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Type</th>
                                <th>Mode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $j = 1; @endphp
                            @forelse($sessions->where('status','Scheduled') as $session)
                            <tr>
                                <td>{{ $j++ }}</td>
                                <td>
                                    @if($session->patient)
                                        @php
                                            $pf = trim(($session->patient->first_name ?? '') . ' ' . ($session->patient->last_name ?? ''));
                                        @endphp
                                        {{ $pf !== '' ? $pf : ($session->patient->name ?? 'Unnamed') }}
                                    @else
                                        <em>Patient removed</em>
                                    @endif
                                </td>
                                <td>{{ $session->clinician?->name ?? '—' }}</td>
                                <td>{{ optional($session->session_start)->format('d M Y, H:i') }}</td>
                                <td>{{ $session->session_end ? $session->session_end->format('d M Y, H:i') : '-' }}</td>
                                <td>{{ ucfirst($session->session_type) }}</td>
                                <td>{{ ucfirst($session->mode) }}</td>
                                <td>
                                    <a href="{{ route('therapy-sessions.show', $session->id) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No scheduled sessions found (on this page).</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="text-muted small">
                        Note: This tab filters the current page of results. Use the "All Sessions" tab and pagination to navigate.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
