@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">User Role Management</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Current Role</th>
                <th>Assign New Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->roles->isNotEmpty())
                        {{ $user->roles->pluck('name')->implode(', ') }}
                    @else
                        <span class="text-muted">No role assigned</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('users.assignRole', $user->id) }}" method="POST">
                        @csrf
                        <select name="role" class="form-select form-select-sm">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" 
                                    {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                </td>
                <td>
                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
