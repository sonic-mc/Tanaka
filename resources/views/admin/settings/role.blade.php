@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">🔐 Role & Permission Management</h2>

    {{-- Create Role --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Create New Role</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="role_name" class="form-label">Role Name</label>
                    <input type="text" name="name" id="role_name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Create Role</button>
            </form>
        </div>
    </div>

    {{-- Create Permission --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Create New Permission</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.permissions.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="permission_name" class="form-label">Permission Name</label>
                    <input type="text" name="name" id="permission_name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Create Permission</button>
            </form>
        </div>
    </div>

    {{-- Assign Permissions to Role --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Assign Permissions to Role</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.assign-permissions') }}">
                @csrf
                <div class="mb-3">
                    <label for="role_select" class="form-label">Select Role</label>
                    <select name="role_id" id="role_select" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Permissions</label>
                    @foreach($permissions as $permission)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}">
                            <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary">Assign Permissions</button>
            </form>
        </div>
    </div>

    {{-- Assign Role to User --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Assign Role to User</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.assign-role') }}">
                @csrf
                <div class="mb-3">
                    <label for="user_select" class="form-label">Select User</label>
                    <select name="user_id" id="user_select" class="form-select" required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="role_assign" class="form-label">Select Role</label>
                    <select name="role_id" id="role_assign" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Assign Role</button>
            </form>
        </div>
    </div>
</div>
@endsection
