<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Psych Monitor') }}</title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="d-flex flex-column flex-lg-row" style="min-height: 100vh;">
        @php
            use Illuminate\Support\Facades\Auth;
            use Illuminate\Support\Facades\Request;

            $role = Auth::user()->role ?? 'guest';
            $name = Auth::user()->name ?? 'User';

            function isActive($pattern) {
                return Request::is($pattern) ? 'active' : '';
            }
        @endphp

        <!-- Sidebar -->
        <nav class="bg-primary text-white p-3 shadow-lg flex-shrink-0" style="
            width: 250px;
            backdrop-filter: blur(10px);
            background-color: rgba(13, 110, 253, 0.85);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        ">
            <h4 class="text-white mb-4">
                <i class="bi bi-heart-pulse-fill me-2"></i>{{ config('app.name', 'Psych Monitor') }}
            </h4>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link text-white {{ isActive('dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>

                @if(in_array($role, ['psychiatrist','nurse']))
                    <li class="nav-item">
                        <a href="{{ route('patients.index') }}" class="nav-link text-white {{ isActive('patients*') }}">
                            <i class="bi bi-person-lines-fill me-2"></i> Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('evaluations.index') }}" class="nav-link text-white {{ isActive('evaluations*') }}">
                            <i class="bi bi-clipboard-pulse me-2"></i> Evaluations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('progress-reports.index') }}" class="nav-link text-white {{ isActive('progress-reports*') }}">
                            <i class="bi bi-bar-chart-line-fill me-2"></i> Progress Monitoring
                        </a>
                    </li>
                @endif

                @if($role === 'psychiatrist')
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">
                            <i class="bi bi-lightbulb-fill me-2"></i> Decision Support
                        </a>
                    </li>
                @endif

                @if(in_array($role, ['psychiatrist','nurse']))
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">
                            <i class="bi bi-file-earmark-text-fill me-2"></i> Reports
                        </a>
                    </li>
                @endif

                @if($role === 'admin')
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">
                            <i class="bi bi-people-fill me-2"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">
                            <i class="bi bi-journal-text me-2"></i> System Activity Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">
                            <i class="bi bi-cloud-arrow-down-fill me-2"></i> Data Backup & Restore
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">
                            <i class="bi bi-credit-card-2-front-fill me-2"></i> Billing & Payments
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3 py-2 shadow-sm">
                <div class="container-fluid">
                    <!-- Back Button -->
                    <button class="btn btn-outline-secondary me-3" onclick="window.history.back()">
                        <i class="bi bi-arrow-left"></i>
                    </button>

                    <!-- Greeting -->
                    <span class="me-auto fw-semibold text-muted">
                        👋 Hello, {{ $name }}
                    </span>

                    <!-- Search Bar -->
                    <form class="d-none d-md-flex me-3" role="search">
                        <input class="form-control form-control-sm" type="search" placeholder="Search..." aria-label="Search">
                    </form>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <a class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 me-2"></i>
                            <span class="d-none d-md-inline">{{ $name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person-fill me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear-fill me-2"></i> Settings</a></li>
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

            <!-- Page Heading -->
            @isset($header)
                <div class="bg-light border-bottom px-4 py-3">
                    <h5 class="mb-0">{{ $header }}</h5>
                </div>
            @endisset

            <!-- Page Content -->
            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
