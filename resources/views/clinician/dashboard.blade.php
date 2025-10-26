@extends('layouts.app')

@push('styles')
    <!-- Bootstrap Icons (optional but recommended) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Subtle, modern card styling */
        .stat-card {
            border: 0;
            border-radius: 0.75rem;
            overflow: hidden;
            position: relative;
        }
        .stat-card .card-body {
            position: relative;
            z-index: 2;
        }
        .stat-card .bg-shape {
            position: absolute;
            right: -30px;
            top: -30px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            opacity: 0.15;
            transform: rotate(25deg);
        }
        .card-zoom {
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .card-zoom:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.10)!important;
        }
        .avatar-sm {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 50%;
        }
        .text-muted-small {
            font-size: .875rem;
            color: var(--bs-secondary-color);
        }
        .list-group-flush .list-group-item {
            padding-left: 0;
            padding-right: 0;
        }
    </style>
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="mb-0">Front Desk Dashboard</h3>
        <div class="text-muted-small">Quick hospital overview and shortcuts</div>
    </div>

    <!-- Optional range filter (wire up as needed) -->
    <form method="GET" class="d-flex align-items-center gap-2">
        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
        <button class="btn btn-sm btn-outline-secondary">Apply</button>
        <a href="{{ url()->current() }}" class="btn btn-sm btn-link">Reset</a>
    </form>
</div>

<!-- Stats Row -->
<div class="row g-3">
    <!-- New Patients Today -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card stat-card text-bg-info card-zoom shadow-sm">
            <div class="bg-shape bg-light"></div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold">New Patients Today</span>
                    <i class="bi bi-person-plus fs-4 opacity-75"></i>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <div class="display-6 fw-bold">{{ $newPatientsToday ?? 0 }}</div>
                    <a href="{{ route('patients.create') }}" class="btn btn-light btn-sm">Register</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Admissions -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card stat-card text-bg-primary card-zoom shadow-sm">
            <div class="bg-shape bg-light"></div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold">Active Admissions</span>
                    <i class="bi bi-hospital fs-4 opacity-75"></i>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <div class="display-6 fw-bold">{{ isset($activeAdmissions) ? $activeAdmissions->count() : 0 }}</div>
                    <a href="{{ route('admissions.index') }}" class="btn btn-light btn-sm">View</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Discharges Today -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card stat-card text-bg-success card-zoom shadow-sm">
            <div class="bg-shape bg-light"></div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold">Discharges Today</span>
                    <i class="bi bi-door-open fs-4 opacity-75"></i>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <div class="display-6 fw-bold">{{ isset($dischargesToday) ? $dischargesToday->count() : 0 }}</div>
                    <a href="{{ route('discharges.index', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-light btn-sm">View</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluations Today -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card stat-card text-bg-warning card-zoom shadow-sm">
            <div class="bg-shape bg-light"></div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold">Evaluations Today</span>
                    <i class="bi bi-clipboard-check fs-4 opacity-75"></i>
                </div>
                <div class="d-flex align-items-end justify-content-between">
                    <div class="display-6 fw-bold">{{ isset($pendingEvaluations) ? $pendingEvaluations->count() : 0 }}</div>
                    <a href="{{ route('evaluations.index') }}" class="btn btn-light btn-sm">View</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Middle Row: Quick Actions + Search -->
<div class="row g-3 mt-1">
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm card-zoom">
            <div class="card-header bg-transparent fw-semibold">
                Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('patients.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Register Patient
                    </a>
                    <a href="{{ route('consultation_fees.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-cash-coin me-1"></i> Consultation Fees Management
                    </a>
                    <a href="{{ route('evaluations.create') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-journal-check me-1"></i> Schedule Evaluation
                    </a>
                </div>
                
                <hr>
                <form action="{{ route('patients.index') }}" method="GET" class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Find a patient by code, name, ID...">
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <div class="text-muted-small mt-2">
                    Tip: Use Advanced filter on Patients page to refine by status.
                </div>
            </div>
        </div>
    </div>

    <!-- Today At A Glance (optional counts) -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm card-zoom">
            <div class="card-header bg-transparent fw-semibold">
                Today at a Glance
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Therapy Sessions
                        <span class="badge text-bg-primary">{{  $therapySessionsCount ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Walk-ins
                        <span class="badge text-bg-info">{{ $walkInsToday ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Pending Payments
                        <span class="badge text-bg-warning">{{ $pendingPaymentsToday ?? 0 }}</span>
                    </li>
                </ul>
                <div class="text-muted-small mt-2">
                    These are optional; wire them to your own sources if available.
                </div>
            </div>
        </div>
    </div>

    <!-- Admissions by Ward (optional dataset) -->
    <div class="col-12 col-lg-4">
     <div class="card shadow-sm card-zoom">
        <div class="card-header bg-transparent fw-semibold">
            Recent Admissions
        </div>
        @if(isset($recentAdmissions) && $recentAdmissions->isNotEmpty())
            @foreach($recentAdmissions as $admission)
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $admission->patient->first_name }} {{ $admission->patient->last_name }}
                    </li>
                    </ul>
                    <div class="text-muted-small">
                        Admitted on {{ \Carbon\Carbon::parse($admission->admission_date)->format('M d, Y') }}
                        @if($admission->room_number)
                            — Room {{ $admission->room_number }}
                        @endif
                    </div>
                    <div class="text-muted-small fst-italic">
                        {{ Str::limit($admission->admission_reason, 80) }}
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-muted-small">No recent admissions found.</div>
        @endif
    </div>
</div>
    
</div>

<!-- Bottom Row: Recent Patients (optional) -->
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card shadow-sm card-zoom">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                <span class="fw-semibold">Recent Patients</span>
                <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-secondary">See all</a>
            </div>
            <div class="card-body">
                @if(isset($recentPatients) && $recentPatients->count())
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Code</th>
                                    <th>Gender</th>
                                    <th>DOB</th>
                                    <th>Contact</th>
                                    <th>Registered</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPatients as $p)
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            @if(!empty($p->photo))
                                                <img src="{{ asset('storage/'.$p->photo) }}" class="avatar-sm" alt="photo">
                                            @else
                                                <div class="bg-secondary-subtle border rounded-circle d-inline-block" style="width:42px;height:42px;"></div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-truncate">{{ $p->full_name ?? ($p->first_name.' '.$p->last_name) }}</div>
                                                <div class="text-muted-small">{{ $p->email ?: '—' }}</div>
                                            </div>
                                        </td>
                                        <td><span class="badge text-bg-light border">{{ $p->patient_code }}</span></td>
                                        <td>{{ $p->gender ? ucfirst($p->gender) : '—' }}</td>
                                        <td>{{ $p->dob ? $p->dob->format('Y-m-d') : '—' }}</td>
                                        <td>{{ $p->contact_number ?: '—' }}</td>
                                        <td>{{ $p->created_at?->diffForHumans() ?: '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('patients.show', $p->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="{{ route('patients.edit', $p->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted-small">No recent patients to display.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
