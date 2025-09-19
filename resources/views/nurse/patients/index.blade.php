@extends('layouts.app')

@section('header')
    Patients
@endsection

@section('content')
<div class="container">
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" id="patientTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned" type="button" role="tab">
                Assigned to Me
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                All Patients
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="patientTabsContent">
        <!-- Assigned Patients -->
        <div class="tab-pane fade show active" id="assigned" role="tabpanel">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="assigned_search" class="form-control" placeholder="Search name or code..." value="{{ request('assigned_search') }}">
                </div>
                <div class="col-md-2">
                    <select name="assigned_status" class="form-select">
                        <option value="">Status</option>
                        <option value="active" {{ request('assigned_status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="discharged" {{ request('assigned_status') == 'discharged' ? 'selected' : '' }}>Discharged</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_gender" class="form-select">
                        <option value="">Gender</option>
                        <option value="male" {{ request('assigned_gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('assigned_gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ request('assigned_gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_care_level" class="form-select">
                        <option value="">Care Level</option>
                        @foreach($careLevels as $level)
                            <option value="{{ $level->id }}" {{ request('assigned_care_level') == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Care Level</th>
                        <th>Status</th>
                        <th>Room</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedPatients as $patient)
                        <tr>
                            <td>{{ $patient->patient_code }}</td>
                            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                            <td>{{ $patient->careLevel->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($patient->status) }}</span></td>
                            <td>{{ $patient->room_number }}</td>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No assigned patients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- All Patients -->
        <div class="tab-pane fade" id="all" role="tabpanel">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="all_search" class="form-control" placeholder="Search name or code..." value="{{ request('all_search') }}">
                </div>
                <div class="col-md-2">
                    <select name="all_status" class="form-select">
                        <option value="">Status</option>
                        <option value="active" {{ request('all_status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="discharged" {{ request('all_status') == 'discharged' ? 'selected' : '' }}>Discharged</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="all_gender" class="form-select">
                        <option value="">Gender</option>
                        <option value="male" {{ request('all_gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('all_gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ request('all_gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="all_care_level" class="form-select">
                        <option value="">Care Level</option>
                        @foreach($careLevels as $level)
                            <option value="{{ $level->id }}" {{ request('all_care_level') == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Care Level</th>
                        <th>Status</th>
                        <th>Room</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allPatients as $patient)
                        <tr>
                            <td>{{ $patient->patient_code }}</td>
                            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                            <td>{{ $patient->careLevel->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($patient->status) }}</span></td>
                            <td>{{ $patient->room_number }}</td>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No patients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
