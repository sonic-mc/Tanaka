@extends('layouts.app')

@section('title', 'Nurse Dashboard')

@section('content')
<div id="nurseDashboard" class="dashboard-section">

    <div class="row">
        <!-- Sidebar -->
        

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-muted mb-1">{{ now()->format('l, d F Y') }}</p>
                    <h2 class="fw-bold mb-1">Good Morning, Nurse Sarah</h2>
                    <p class="text-muted">You have <span class="text-danger fw-bold">12 Patients</span> today</p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="position-relative me-3">
                        <i class="bi bi-bell fs-4"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=Sarah+Johnson&background=00d4aa&color=fff" class="rounded-circle" width="45" alt="Profile">
                </div>
            </div>

            <!-- Welcome Card -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-2">Patient Care Overview</h4>
                        <p class="mb-0 text-muted">Monitor and track patient progress, incidents, and therapy attendance</p>
                    </div>
                    <div style="font-size: 70px;">👩‍⚕️</div>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #00d4aa, #00a693);">
                                <i class="bi bi-people text-white"></i>
                            </div>
                            <h4 class="fw-bold mb-0">12</h4>
                            <small class="text-muted">Patients</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #f43f5e, #ec4899);">
                                <i class="bi bi-calendar-check text-white"></i>
                            </div>
                            <h4 class="fw-bold mb-0">8</h4>
                            <small class="text-muted">Appointments</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="mb-2 rounded-circle d-inline-block p-3" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                <i class="bi bi-clipboard-check text-white"></i>
                            </div>
                            <h4 class="fw-bold mb-0">24</h4>
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
                            <h4 class="fw-bold mb-0">3</h4>
                            <small class="text-muted">Incidents</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Patient Requests -->
                <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold">Patient Requests</h5>
                        <a href="#" class="text-primary text-decoration-none">Show All →</a>
                    </div>

                    @foreach ([
                        ['name' => 'Emily Chen', 'case' => 'Anxiety Management', 'date' => '10 May 2025', 'time' => '10:00 AM'],
                        ['name' => 'James Wilson', 'case' => 'Depression Treatment', 'date' => '10 May 2025', 'time' => '02:00 PM'],
                        ['name' => 'Maria Garcia', 'case' => 'PTSD Recovery', 'date' => '11 May 2025', 'time' => '11:00 AM']
                    ] as $req)
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($req['name']) }}&background=00d4aa&color=fff"
                                    class="rounded-circle me-3" width="50" alt="">
                                <div>
                                    <h6 class="mb-1">{{ $req['name'] }}</h6>
                                    <small class="text-muted">{{ $req['case'] }}</small>
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $req['date'] }}
                                </span>
                                <span class="badge bg-warning text-dark">{{ $req['time'] }}</span>
                                <button class="btn btn-sm btn-outline-primary ms-2">See Details</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Calendar & Quick Actions -->
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3 text-center">
                                <i class="bi bi-chevron-left me-2"></i> JUNE 2025 <i class="bi bi-chevron-right ms-2"></i>
                            </h6>
                            <div class="text-center text-muted">[Calendar Widget]</div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-danger">
                                    Report Incident <i class="bi bi-exclamation-circle ms-2"></i>
                                </button>
                                <button class="btn btn-success">
                                    Add Progress Note <i class="bi bi-plus-circle ms-2"></i>
                                </button>
                                <button class="btn btn-warning">
                                    Update Attendance <i class="bi bi-check2-square ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection
