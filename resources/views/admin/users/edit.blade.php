@extends('layouts.app')

@section('header')
    Edit User: {{ $user->name }}
@endsection

@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><input type="text" name="name" class="form-control" value="{{ $user->name }}"></div>
        <div class="col-md-6"><input type="email" name="email" class="form-control" value="{{ $user->email }}"></div>
        <div class="col-md-6">
            <select name="role" class="form-select">
                @foreach(['admin', 'psychiatrist', 'nurse', 'clinician'] as $role)
                    <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <select name="active" class="form-select">
                <option value="1" @selected($user->active)>Active</option>
                <option value="0" @selected(!$user->active)>Inactive</option>
            </select>
        </div>
        <div class="col-md-12"><button class="btn btn-primary">Update User</button></div>
    </div>
</form>
@endsection
