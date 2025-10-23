@extends('layouts.app')
@section('content')
<h3>Admissions</h3>
<a href="{{ route('admissions.create') }}" class="btn btn-primary mb-3">New Admission</a>
<table class="table">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Date</th>
            <th>Status</th>
            <th>Room</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($admissions as $admission)
        <tr>
            <td>{{ $admission->patient->first_name }} {{ $admission->patient->last_name }}</td>
            <td>{{ $admission->admission_date }}</td>
            <td>{{ ucfirst($admission->status) }}</td>
            <td>{{ $admission->room_number }}</td>
            <td>
                <a href="{{ route('admissions.show', $admission) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('admissions.edit', $admission) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admissions.destroy', $admission) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this admission?')">Delete</button>
                </form>
                @if($admission->status === 'active')
                <form action="{{ route('admissions.discharge', $admission) }}" method="POST" style="display:inline;">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-secondary">Discharge</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
