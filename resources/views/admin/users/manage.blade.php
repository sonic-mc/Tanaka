@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary">Manage Users</h2>
    <span class="text-muted">Admin Panel</span>
</div>
@endsection

@section('content')
@php
    $activeTab = request('tab', 'view-users');
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="userTabs" role="tablist">
            @foreach([
                'view-users' => 'View All Users',
                'manage-users' => 'Create / Edit / Deactivate',
                'access-reset' => 'Access & Password Reset',
                'audit-logs' => 'Audit Logs'
            ] as $tab => $label)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab === $tab ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => $tab, 'page' => null]) }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>

        <div class="tab-content" id="userTabsContent">
            {{-- Tab: View All Users --}}
            <div class="tab-pane fade {{ $activeTab === 'view-users' ? 'show active' : '' }}" id="view-users" role="tabpanel">
                <form method="GET" class="mb-3">
                    <input type="hidden" name="tab" value="view-users">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="role" class="form-select form-select-sm">
                                <option value="">All Roles</option>
                                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="psychiatrist" {{ request('role') === 'psychiatrist' ? 'selected' : '' }}>Psychiatrist</option>
                                <option value="nurse" {{ request('role') === 'nurse' ? 'selected' : '' }}>Nurse</option>
                                <option value="clinician" {{ request('role') === 'clinician' ? 'selected' : '' }}>Clinician</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search by name or email">
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Filter</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge bg-secondary rounded-pill">{{ ucfirst($user->role) }}</span></td>
                                <td>{{ optional($user->created_at)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>

                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-2">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>

            {{-- Tab: Create / Edit / Deactivate --}}
            <div class="tab-pane fade {{ $activeTab === 'manage-users' ? 'show active' : '' }}" id="manage-users" role="tabpanel">
                <form method="POST" action="{{ route('admin.users.store') }}" class="mb-3">
                    @csrf
                    <input type="hidden" name="tab" value="manage-users">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Name" required>
                        </div>
                        <div class="col-md-4">
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="col-md-4">
                            <select name="role" class="form-select" required>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="psychiatrist" {{ old('role') === 'psychiatrist' ? 'selected' : '' }}>Psychiatrist</option>
                                <option value="nurse" {{ old('role') === 'nurse' ? 'selected' : '' }}>Nurse</option>
                                <option value="clinician" {{ old('role') === 'clinician' ? 'selected' : '' }}>Clinician</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="col-md-4">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                        </div>

                        <div class="col-md-4">
                            <button class="btn btn-success w-100">Create User</button>
                        </div>
                    </div>
                </form>

                <hr>

                {{-- Quick load existing user for editing/deactivation --}}
                <form method="GET" class="mb-3" action="{{ route('admin.users.index') }}">
                    <input type="hidden" name="tab" value="manage-users">
                    <div class="input-group">
                        <select name="user_id" class="form-select">
                            <option value="">Select User</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-primary">Load</button>
                    </div>
                </form>

                <p class="text-muted">Once a user is selected (use the quick load), you can edit or deactivate them via the Edit page.</p>
            </div>

            {{-- Tab: Access & Password Reset --}}
            <div class="tab-pane fade {{ $activeTab === 'access-reset' ? 'show active' : '' }}" id="access-reset" role="tabpanel">
                <form method="POST" action="{{ route('admin.users.resetPassword') }}">
                    @csrf
                    <input type="hidden" name="tab" value="access-reset">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <select name="user_id" class="form-select" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-danger w-100">Reset</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tab: Audit Logs --}}
            <div class="tab-pane fade {{ $activeTab === 'audit-logs' ? 'show active' : '' }}" id="audit-logs" role="tabpanel">
                <form method="GET" class="mb-3" action="{{ route('admin.users.index') }}">
                    <input type="hidden" name="tab" value="audit-logs">
                    <div class="input-group">
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-secondary">Filter Logs</button>
                    </div>
                </form>

                @if(isset($logs) && $logs && $logs->count())
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                <td>{{ optional($log->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $logs->appends(['tab' => 'audit-logs'])->links() }}
                </div>
                @else
                    <p class="text-muted">No audit logs found for the selected user.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
