<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Psych Monitor') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="d-flex" style="min-height: 100vh;">
        <!-- Sidebar -->
        <nav class="bg-primary text-white p-3" style="width: 250px;">
            <h4 class="text-white mb-4">{{ config('app.name', 'Psych Monitor') }}</h4>

            @php
                $role = Auth::user()->role ?? 'guest';
            @endphp

            <ul class="nav flex-column">

                {{-- Common Links (All Roles) --}}
                <li class="nav-item">
                    <a href="#" class="nav-link text-white">Dashboard</a>
                </li>

                {{-- Psychiatrist & Nurse --}}
                @if(in_array($role, ['psychiatrist','nurse']))
                    <li class="nav-item">
                        <a href="{{ route('patients.index') }}" class="nav-link text-white">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('evaluations.index') }}" class="nav-link text-white">Evaluations</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">Progress Monitoring</a>
                    </li>
                @endif

                {{-- Psychiatrist only --}}
                @if($role === 'psychiatrist')
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">Decision Support</a>
                    </li>
                @endif

                {{-- All clinical staff --}}
                @if(in_array($role, ['psychiatrist','nurse']))
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">Reports</a>
                    </li>
                @endif

                {{-- Admin only --}}
                @if($role === 'admin')
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">System Activity Logs</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">Data Backup & Restore</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-white">Billing & Payments</a>
                    </li>
                @endif

            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
                <div class="container-fluid justify-content-end">
                    <span class="me-3">EN</span>
                    <div class="dropdown">
                        <a class="dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
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
