@extends('layouts.app')

@section('title', 'Nurse Dashboard')

@section('content')
<div id="nurseDashboard" class="dashboard-section">
    <style>
        /* Keep your existing styles ... */
         /* Calendar styles */
         .calendar-container .card.perspective {
            perspective: 1000px;
            border: 0;
        }
        .calendar-container .card-inner {
            position: relative;
            width: 100%;
            min-height: 420px;
            transform-style: preserve-3d;
            transition: transform 0.6s ease;
        }
        .calendar-container .card-inner.flipped {
            transform: rotateY(180deg);
        }
        .calendar-container .card-face {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            background: #fff;
            border-radius: .5rem;
            overflow: hidden;
        }
        .calendar-container .card-back {
            transform: rotateY(180deg);
        }

        .calendar-header {
            border-bottom: 1px solid #f1f3f5;
            background: #fafbfc;
        }
        .calendar-nav {
            cursor: pointer;
            font-size: 1.1rem;
            user-select: none;
            padding: .25rem .5rem;
            border-radius: .25rem;
        }
        .calendar-nav:hover {
            background: #eef2f7;
        }

        .day-header {
            color: #6b7280;
            font-size: .85rem;
            padding-bottom: .25rem;
        }

        .calendar-day {
            height: 44px;
            line-height: 44px;
            text-align: center;
            margin: 2px 0;
            border-radius: .375rem;
            font-weight: 600;
            color: #111827;
            position: relative;
        }
        .calendar-day.other-month {
            color: #9ca3af;
        }
        .calendar-day.today {
            outline: 2px solid #3b82f6;
            outline-offset: -2px;
            background: #eff6ff;
        }
        .calendar-day.has-event::after {
            content: "";
            width: 6px;
            height: 6px;
            background: #ef4444;
            border-radius: 50%;
            position: absolute;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
        }

        .events-indicator {
            position: absolute;
            top: .5rem;
            right: .75rem;
            z-index: 2;
            background: rgba(59,130,246,0.1);
            color: #1d4ed8;
            border: 1px solid rgba(59,130,246,0.25);
            border-radius: 9999px;
            font-size: .8rem;
            padding: .2rem .6rem;
        }
    </style>

    <div class="row">
        <div class="container-fluid px-4 py-4">
            <div class="w-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    @php
                        use Carbon\Carbon;
                        $hour = Carbon::now()->format('H');
                        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
                    @endphp
                    <div>
                        <p class="text-muted mb-1">{{ now()->format('l, d F Y') }}</p>
                        <h2 class="fw-bold mb-1">{{ $greeting }}, {{ Auth::user()->name }}</h2>
                        <p class="text-muted">
                            You have <span class="text-danger fw-bold">{{ $patientsCount ?? 0 }} patients</span> assigned to you today.
                        </p>
                    </div>
                </div>

                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-2">Patient Care Overview</h4>
                            <p class="mb-0 text-muted">Monitor and track patient progress, incidents, and therapy attendance</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div style="font-size: 70px;">👩‍⚕️</div>
                            
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #00d4aa, #00a693);">
                                    <i class="bi bi-people text-white"></i>
                                </div>
                                <h4 class="fw-bold mb-0">{{ $admissionsCount }}</h4>
                                <small class="text-muted">Patients</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #6f42c1, #5a32a3);">
                                    <i class="bi bi-chat-heart text-white"></i>
                                </div>
                                <h4 class="fw-bold mb-0">{{ $therapySessionsCount }}</h4>
                                <small class="text-muted">Therapy Sessions</small>
                            </div>
                        </div>
                    </div>
                    

                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                    <i class="bi bi-clipboard-check text-white"></i>
                                </div>
                                <h4 class="fw-bold mb-0">{{ $progressreportCount }}</h4>
                                <small class="text-muted">Reports Today</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                                    <i class="bi bi-exclamation-triangle text-white"></i>
                                </div>
                                <h4 class="fw-bold mb-0">{{ $incidentsCount }}</h4>
                                <small class="text-muted">Incidents</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Assigned Admissions -->
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold">My Assigned Patients</h5>
                            <a href="{{ route('patients.index') }}" class="text-primary text-decoration-none">Show All →</a>
                        </div>

                        @forelse ($assignedAdmissions as $adm)
                            <div class="request-card card mb-3 shadow-sm border-0">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        @php
                                            $p = $adm->patient;
                                            $name = $p ? ($p->first_name . ' ' . $p->last_name) : 'Patient';
                                        @endphp
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=00d4aa&color=fff"
                                             class="rounded-circle me-3" width="50" alt="{{ $name }}">
                                        <div>
                                            <h6 class="mb-1">{{ $name }}</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $adm->admission_reason ?? 'General Care' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ optional($adm->admission_date)->format('d M Y') }}
                                        </span>
                                        <span class="badge bg-warning text-dark">
                                            {{ $adm->room_number ?? 'No Room' }}
                                        </span>
                                        @if($p)
                                            <a href="{{ route('patients.show', $p->id) }}" class="btn btn-sm btn-outline-primary ms-2">
                                                See Details
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">
                                No patients currently assigned to you.
                            </div>
                        @endforelse
                    </div>

                    <!-- Calendar & Quick Actions (unchanged core structure) -->
                    <div class="col-md-4 calendar-container">
                        {{-- Keep your calendar card and scripts as-is --}}
                        {{-- Quick Actions --}}
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-lightning-charge-fill me-2 text-primary"></i> Quick Actions
                                </h6>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-danger d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-exclamation-circle me-2"></i> Report Incident</span>
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>
                                    <a href="{{ route('progress-reports.index') }}" class="btn btn-outline-success d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-plus-circle me-2"></i> Add Progress Note</span>
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>
                                    <a href="{{ route('therapy-sessions.index') }}" class="btn btn-outline-warning d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-check2-square me-2"></i> Create Therapy Session</span>
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> <!-- /Calendar & Quick Actions -->
                </div> <!-- /row -->
            </div>
        </div>
    </div>
</div>


@endsection