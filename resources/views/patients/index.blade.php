@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Registered Patients</h3>
    @if(Auth::user() && Auth::user()->role === 'clinician')
    <a href="{{ route('patients.create') }}" class="btn btn-primary">Register New Patient</a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="GET" action="{{ route('patients.index') }}" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" name="q" class="form-control" placeholder="Search by code, name, ID, passport, email, contact..." value="{{ request('q') }}">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="active" {{ request('status','active') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>Archived</option>
            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
        </select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-secondary w-100">Filter</button>
    </div>
</form>

<table class="table table-bordered align-middle">
    <thead>
        <tr>
            <th>Avatar</th>
            <th>Code</th>
            <th>Name</th>
            <th>Gender</th>
            <th>DOB</th>
            <th>Contact</th>
            <th>Status</th>
            <th style="width: 260px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($patients as $patient)
            @php
                $g = strtolower($patient->gender ?? '');
                $avatarPath = match ($g) {
                    'female' => 'images/download (1).jpeg',
                    'male'   => 'images/download (2).jpeg',
                    default  => 'images/download (2).jpeg', // default/fallback
                };
                // Server-side safety: if the chosen file is missing, fall back
                if (!file_exists(public_path($avatarPath))) {
                    $avatarPath = 'images/download (2).jpeg';
                }
            @endphp
            <tr @if($patient->deleted_at) class="table-warning" @endif>
                <td>
                    <img
                        src="{{ asset($avatarPath) }}"
                        alt="Avatar"
                        width="48"
                        height="48"
                        class="rounded-circle object-fit-cover"
                        onerror="this.onerror=null;this.src='{{ asset('images/download (2).jpeg') }}';"
                    >
                </td>
                <td>{{ $patient->patient_code }}</td>
                <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                <td>{{ ucfirst($patient->gender) }}</td>
                <td>{{ optional($patient->dob)->format('Y-m-d') }}</td>
                <td>{{ $patient->contact_number }}</td>
                <td>{{ $patient->deleted_at ? 'Archived' : 'Active' }}</td>
                <td>
                    <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-sm btn-info">View</a>
                    @if(!$patient->deleted_at)
                        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('patients.destroy', $patient) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Archive this patient?')">Archive</button>
                        </form>
                    @else
                        <form action="{{ route('patients.restore', $patient->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-secondary" onclick="return confirm('Restore this patient?')">Restore</button>
                        </form>
                        <form action="{{ route('patients.force-delete', $patient->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this patient? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete Permanently</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center">No patients found.</td></tr>
        @endforelse
        </tbody>
        
</table>

{{ $patients->links() }}
@endsection
