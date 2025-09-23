<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Psych Monitor') }}</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Custom Theme -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar, .fixed-sidebar {
            background-color: #0B1F3A;
            color: #fff;
        }

        .navbar .text-muted,
        .dropdown-toggle,
        .dropdown-menu a {
            color: #fff !important;
        }

        .navbar .form-control {
            border: 1px solid #fff;
            background-color: transparent;
            color: #fff;
        }

        .navbar .form-control::placeholder {
            color: #B0B8C1;
        }

        .navbar .btn-outline-secondary {
            border-color: #fff;
            color: #fff;
        }

        .navbar .btn-outline-secondary:hover {
            background-color: #FF4C4C;
            border-color: #FF4C4C;
        }

        .fixed-sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 70px;
            background-color: #0B1F3A;
            overflow-y: auto;
        }

        .fixed-sidebar .nav-link {
            color: #fff;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .fixed-sidebar .nav-link.active {
            background-color: #FF4C4C;
            border-radius: 0.375rem;
        }

        .main-content {
            margin-left: 250px;
            padding-top: 70px;
            background-color: #fff;
            border-top: 4px solid #FF4C4C;
            min-height: 100vh;
        }

        .dropdown-menu {
            background-color: #0B1F3A;
        }

        .dropdown-item:hover {
            background-color: #FF4C4C;
            color: #fff;
        }
    </style>
</head>
<body>
    @php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Request;

    $user = Auth::user();
    $role = $user ? $user->role : 'guest';
    $name = $user ? $user->name : 'User';

    function isActive($pattern) {
        return Request::is($pattern) ? 'active' : '';
    }
@endphp


    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
        <div class="container-fluid px-3 py-2">
            <button class="btn btn-outline-secondary me-3" onclick="window.history.back()">
                <i class="bi bi-arrow-left"></i>
            </button>

            <span class="me-auto fw-semibold text-muted">
                👋 Hello, {{ $name }}
            </span>

            <form class="d-none d-md-flex me-3" role="search">
                <input class="form-control form-control-sm" type="search" placeholder="Search..." aria-label="Search">
            </form>

            <div class="dropdown">
                <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5 me-2"></i>
                    <span class="d-none d-md-inline">{{ $name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person-fill me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.roles-permissions') }}"><i class="bi bi-gear-fill me-2"></i> Settings</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-question-circle-fill me-2"></i> Help</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

   <!-- Sidebar -->
<nav class="fixed-sidebar p-3">
    <h4 class="text-white mb-4">
        <i class="bi bi-heart-pulse-fill me-2"></i>{{ config('app.name', 'Psych Monitor') }}
    </h4>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ isActive('dashboard') }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        @if(auth()->user()->hasAnyRole(['psychiatrist','nurse']))
            <li class="nav-item"><a href="{{ route('patients.index') }}" class="nav-link {{ isActive('patients*') }}"><i class="bi bi-person-lines-fill me-2"></i> Patients</a></li>
            <li class="nav-item"><a href="{{ route('evaluations.index') }}" class="nav-link {{ isActive('evaluations*') }}"><i class="bi bi-clipboard-pulse me-2"></i> Evaluations</a></li>
            <li class="nav-item"><a href="{{ route('progress-reports.index') }}" class="nav-link {{ isActive('progress-reports*') }}"><i class="bi bi-bar-chart-line-fill me-2"></i> Progress Monitoring</a></li>
            <li class="nav-item"><a href="{{ route('therapy-sessions.index') }}" class="nav-link {{ isActive('therapy-sessions*') }}"><i class="bi bi-calendar-check me-2"></i> Therapy Sessions</a></li>
            <li class="nav-item"><a href="{{ route('incidents.index') }}" class="nav-link {{ isActive('incidents*') }}"><i class="bi bi-exclamation-triangle me-2"></i> Incidents</a></li>
            <li class="nav-item"><a href="{{ route('notifications.index') }}" class="nav-link {{ isActive('notifications*') }}"><i class="bi bi-bell me-2"></i> Notifications</a></li>
        @endif

        @if(auth()->user()->hasRole('psychiatrist'))
            <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-lightbulb-fill me-2"></i> Decision Support</a></li>
        @endif

        @if(auth()->user()->hasAnyRole(['psychiatrist','nurse']))
            <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-file-earmark-text-fill me-2"></i> Reports</a></li>
        @endif

        @if(auth()->user()->hasRole('admin'))
            <li class="nav-item"><a href="{{ route('admin.users.index')}}" class="nav-link"><i class="bi bi-people-fill me-2"></i> User Management</a></li>
            <li class="nav-item"><a href="{{ route('admin.logs.index')}}" class="nav-link"><i class="bi bi-journal-text me-2"></i> System Activity Logs</a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-cloud-arrow-down-fill me-2"></i> Data Backup & Restore</a></li>
            <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-credit-card-2-front-fill me-2"></i> Billing & Payments</a></li>
        @endif
    </ul>
</nav>


    <!-- Main Content -->
    <div class="main-content">
        @isset($header)
            <div class="bg-light border-bottom px-4 py-3">
                <h5 class="mb-0">{{ $header }}</h5>
            </div>
        @endisset

        <main class="p-4">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('incidentsByMonthChart'), {
                type: 'bar',
                data: {
                    labels: incidentsByMonthLabels,
                    datasets: [{
                        label: 'Incidents',
                        data: incidentsByMonthData,
                        backgroundColor: '#0d6efd'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
    
            new Chart(document.getElementById('recurrenceChart'), {
                type: 'doughnut',
                data: {
                    labels: recurrenceLabels,
                    datasets: [{
                        label: 'Recurrence',
                        data: recurrenceData,
                        backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0d6efd', '#6f42c1']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
    

</body>
</html>
