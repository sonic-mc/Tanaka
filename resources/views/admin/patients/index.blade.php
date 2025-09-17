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
            <th>Status</th>
            <th>Care Level</th>
            <th>Admitted By</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($patients as $patient)
        <tr>
            <td>{{ $patient->patient_code }}</td>
            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
            <td><span class="badge bg-info">{{ ucfirst($patient->status) }}</span></td>
            <td>{{ $patient->careLevel->name ?? '—' }}</td>
            <td>{{ $patient->admittedBy->name ?? '—' }}</td>
            <td>
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">View</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
