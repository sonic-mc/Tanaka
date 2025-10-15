@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary">Manage Users</h2>
    <span class="text-muted">Admin Panel</span>
</div>
@endsection

@section('content')
@php
    $activeTab = request('tab') ?? 'view-users';
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="userTabs" role="tablist">
            @foreach([
                'view-users' => 'View All Users',
                'manage-users' => 'Create / Edit / Deactivate',
                // 'assign-roles' => 'Assign & Update Roles',
                'access-reset' => 'Access & Password Reset',
                'audit-logs' => 'Audit Logs'
            ] as $tab => $label)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab === $tab ? 'active' : '' }}"
                   href="?tab={{ $tab }}">{{ $label }}</a>
            </li>
            @endforeach
        </ul>

        <div class="tab-content" id="userTabsContent">
            {{-- Tab 1: View All Users --}}
            <div class="tab-pane fade {{ $activeTab === 'view-users' ? 'show active' : '' }}" id="view-users" role="tabpanel">
                <form method="GET" class="mb-3">
                    <input type="hidden" name="tab" value="view-users">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="role" class="form-select form-select-sm">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="psychiatrist">Psychiatrist</option>
                                <option value="nurse">Nurse</option>
                                <option value="clinician">Clinician</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or email">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary btn-sm w-100">Filter</button>
                        </div>
                    </div>
                </form>

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
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
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
            </div>

            {{-- Tab 2: Create / Edit / Deactivate --}}
            <div class="tab-pane fade {{ $activeTab === 'manage-users' ? 'show active' : '' }}" id="manage-users" role="tabpanel">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <input type="hidden" name="tab" value="manage-users">
                    <div class="row g-3">
                        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Name"></div>
                        <div class="col-md-4"><input type="email" name="email" class="form-control" placeholder="Email"></div>
                        <div class="col-md-4">
                            <select name="role" class="form-select">
                                <option value="admin">Admin</option>
                                <option value="psychiatrist">Psychiatrist</option>
                                <option value="nurse">Nurse</option>
                                <option value="clinician">Clinician</option>
                            </select>
                        </div>
                        <div class="col-md-4"><input type="password" name="password" class="form-control" placeholder="Password"></div>
                        <div class="col-md-4"><input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password"></div>
                        <div class="col-md-4"><button class="btn btn-success w-100">Create User</button></div>
                    </div>
                </form>

                <hr>

                <form method="GET" class="mb-3">
                    <input type="hidden" name="tab" value="manage-users">
                    <div class="input-group">
                        <select name="user_id" class="form-select">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-primary">Load</button>
                    </div>
                </form>

                <p class="text-muted">Once a user is selected, you can edit their details or deactivate them.</p>
            </div>

            {{-- Tab 3: Assign & Update Roles
            <div class="tab-pane fade {{ $activeTab === 'assign-roles' ? 'show active' : '' }}" id="assign-roles" role="tabpanel">
                <form method="POST" action="{{ route('admin.users.updateRole') }}">
                    @csrf
                    <input type="hidden" name="tab" value="assign-roles">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <select name="user_id" class="form-select">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="new_role" class="form-select">
                                <option value="admin">Admin</option>
                                <option value="psychiatrist">Psychiatrist</option>
                                <option value="nurse">Nurse</option>
                                <option value="clinician">Clinician</option>
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-warning w-100">Update Role</button></div>
                    </div>
                </form>
            </div> --}}

            {{-- Tab 4: Access & Password Reset --}}
            <div class="tab-pane fade {{ $activeTab === 'access-reset' ? 'show active' : '' }}" id="access-reset" role="tabpanel">
                <form method="POST" action="{{ route('admin.users.resetPassword') }}">
                    @csrf
                    <input type="hidden" name="tab" value="access-reset">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <select name="user_id" class="form-select">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><input type="password" name="new_password" class="form-control" placeholder="New Password"></div>
                        <div class="col-md-2"><button class="btn btn-danger w-100">Reset</button></div>
                    </div>
                </form>
            </div>

            {{-- Tab 5: Audit Logs --}}
            <div class="tab-pane fade {{ $activeTab === 'audit-logs' ? 'show active' : '' }}" id="audit-logs" role="tabpanel">
                <form method="GET" class="mb-3">
                    <input type="hidden" name="tab" value="audit-logs">
                    <div class="input-group">
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-secondary">Filter Logs</button>
                    </div>
                </form>

                @if(isset($logs) && $logs->count())
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
                            <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $logs->appends(['tab' => 'audit-logs'])->links() }}
                @else
                    <p class="text-muted">No audit logs found for the selected user.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
