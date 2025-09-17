@extends('layouts.app')

@section('header')
    Patients
@endsection

@section('content')
<table class="table table-hover">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Care Level</th>
            <th>Status</th>
            <th>Room</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($patients as $patient)
        <tr>
            <td>{{ $patient->patient_code }}</td>
            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
            <td>{{ $patient->careLevel->name ?? '—' }}</td>
            <td><span class="badge bg-secondary">{{ ucfirst($patient->status) }}</span></td>
            <td>{{ $patient->room_number }}</td>
            <td>
                <a href="{{ route('nurse.patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">View</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
