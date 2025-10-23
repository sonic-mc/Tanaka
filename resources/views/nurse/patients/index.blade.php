@extends('layouts.app')

@section('header')
    Patients
@endsection

@section('content')
<div class="container">
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" id="patientTabs" role="tablist">
        {{-- Nurse: Assigned patients tab --}}
        @if(auth()->user()->hasRole('nurse'))
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned" type="button" role="tab">
                    Assigned to Me
                </button>
            </li>
        @endif

        {{-- All Patients: visible to everyone --}}
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ auth()->user()->hasRole('nurse') ? '' : 'active' }}" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                All Patients
            </button>
        </li>

        {{-- Psychiatrist: Assigned to Nurses tab --}}
        @if(auth()->user()->hasRole('psychiatrist'))
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nurse-tab" data-bs-toggle="tab" data-bs-target="#nurse" type="button" role="tab">
                    Assigned to Nurses
                </button>
            </li>
        @endif

        {{-- Patient Management Tab (for psychiatrists + admins) --}}
        @if(auth()->user()->hasRole('psychiatrist') || auth()->user()->hasRole('admin'))
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="management-tab" data-bs-toggle="tab" data-bs-target="#management" type="button" role="tab">
                Patient Management
            </button>
        </li>

        <li class="nav-item" role="presentation">
            <button class="nav-link" id="discharged-tab" data-bs-toggle="tab" data-bs-target="#discharged" type="button" role="tab">
                Discharged Patients
            </button>
        </li>
        
        @endif
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="patientTabsContent">

        {{-- Nurse: Assigned Patients --}}
        @if(auth()->user()->hasRole('nurse'))
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
        @endif

        {{-- All Patients (default active if not nurse) --}}
        <div class="tab-pane fade {{ auth()->user()->hasRole('nurse') ? '' : 'show active' }}" id="all" role="tabpanel">
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

        {{-- Psychiatrist: Patients Assigned to Nurses --}}
        @if(auth()->user()->hasRole('psychiatrist'))
        <div class="tab-pane fade" id="nurse" role="tabpanel">
    
            {{-- 🔹 Filters --}}
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="nurse_search" class="form-control" placeholder="Search name or code..." value="{{ request('nurse_search') }}">
                </div>
                <div class="col-md-2">
                    <select name="nurse_status" class="form-select">
                        <option value="">Status</option>
                        <option value="active" {{ request('nurse_status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="discharged" {{ request('nurse_status') == 'discharged' ? 'selected' : '' }}>Discharged</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="nurse_gender" class="form-select">
                        <option value="">Gender</option>
                        <option value="male" {{ request('nurse_gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('nurse_gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ request('nurse_gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="nurse_care_level" class="form-select">
                        <option value="">Care Level</option>
                        @foreach($careLevels as $level)
                            <option value="{{ $level->id }}" {{ request('nurse_care_level') == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>
    
            {{-- 🔹 Unassigned Patients (for assignment) --}}
            <h5 class="mb-3">Unassigned Patients</h5>
            <table class="table table-hover mb-5">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Care Level</th>
                        <th>Status</th>
                        <th>Room</th>
                        <th>Assign Nurse</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unassignedPatients as $patient)
                        <tr>
                            <td>{{ $patient->patient_code }}</td>
                            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                            <td>{{ $patient->careLevel->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($patient->status) }}</span></td>
                            <td>{{ $patient->room_number }}</td>
                            <td>
                                <form method="POST" action="{{ route('patients.assign-nurse', $patient->id) }}">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <select name="nurse_id" class="form-select" required>
                                            <option value="">— Select Nurse —</option>
                                            @foreach($nurses as $nurse)
                                                <option value="{{ $nurse->id }}">{{ $nurse->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-success" type="submit">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No unassigned patients available.</td></tr>
                    @endforelse
                </tbody>
            </table>
    
            {{-- 🔹 Patients Already Assigned to Nurses --}}
            <h5 class="mb-3">Patients Assigned to Nurses</h5>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Care Level</th>
                        <th>Status</th>
                        <th>Room</th>
                        <th>Assigned Nurse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nurseAssignedPatients as $patient)
                        <tr>
                            <td>{{ $patient->patient_code }}</td>
                            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                            <td>{{ $patient->careLevel->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($patient->status) }}</span></td>
                            <td>{{ $patient->room_number }}</td>
                            <td>
                                <form method="POST" action="{{ route('patients.assign-nurse', $patient->id) }}">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <select name="nurse_id" class="form-select">
                                            <option value="">— Select Nurse —</option>
                                            @foreach($nurses as $nurse)
                                                <option value="{{ $nurse->id }}" {{ $patient->assigned_nurse_id == $nurse->id ? 'selected' : '' }}>
                                                    {{ $nurse->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-success" type="submit">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </div>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No patients assigned to nurses found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
       @endif

       {{-- Patient Management --}}
        @if(auth()->user()->hasRole('psychiatrist') || auth()->user()->hasRole('admin'))
        <div class="tab-pane fade" id="management" role="tabpanel">

            <h5 class="mb-3">Manage Patient</h5>

            <form method="GET" action="" id="patientManagementForm" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <select id="patientSelect" class="form-select">
                        <option value="">— Select Patient —</option>
                        @foreach($allPatients as $patient)
                            <option value="{{ $patient->id }}">{{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 d-flex gap-2">
                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="{{ route('patients.discharge', $patient) }}" class="btn btn-sm btn-outline-primary">Discharge</a>
                    {{-- <a id="admitBtn" href="#" class="btn btn-outline-success disabled">Admit</a>
                    <a id="dischargeBtn" href="#" class="btn btn-outline-danger disabled">Discharge</a> --}}
                </div>
                
            </form>
        </div>

        <div class="tab-pane fade" id="discharged" role="tabpanel">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="discharged_search" class="form-control" placeholder="Search name or code..." value="{{ request('discharged_search') }}">
                </div>
                <div class="col-md-2">
                    <select name="discharged_gender" class="form-select">
                        <option value="">Gender</option>
                        <option value="male" {{ request('discharged_gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('discharged_gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ request('discharged_gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="discharged_care_level" class="form-select">
                        <option value="">Care Level</option>
                        @foreach($careLevels as $level)
                            <option value="{{ $level->id }}" {{ request('discharged_care_level') == $level->id ? 'selected' : '' }}>
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
                        <th>Discharged</th>
                        <th>Room</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dischargedPatients as $patient)
                        <tr>
                            <td>{{ $patient->patient_code }}</td>
                            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                            <td>{{ $patient->careLevel->name ?? '—' }}</td>
                            <td><span class="badge bg-danger">{{ ucfirst($patient->status) }}</span></td>
                            <td>{{ $patient->room_number }}</td>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No discharged patients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @endif

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const select = document.getElementById('patientSelect');
                const editBtn = document.getElementById('editBtn');
                const admitBtn = document.getElementById('admitBtn');
                const dischargeBtn = document.getElementById('dischargeBtn');
        
                if (select) {
                    select.addEventListener('change', function () {
                        const patientId = this.value;
        
                        if (patientId) {
                            editBtn.href = `/patients/${patientId}/edit`;
                            admitBtn.href = `/patients/${patientId}/admit`;
                            dischargeBtn.href = `/patients/${patientId}/discharge`;
        
                            editBtn.classList.remove('disabled');
                            admitBtn.classList.remove('disabled');
                            dischargeBtn.classList.remove('disabled');
                        } else {
                            editBtn.href = '#';
                            admitBtn.href = '#';
                            dischargeBtn.href = '#';
        
                            editBtn.classList.add('disabled');
                            admitBtn.classList.add('disabled');
                            dischargeBtn.classList.add('disabled');
                        }
                    });
                }
            });
        </script>
        @endpush
    </div>
</div>
@endsection
