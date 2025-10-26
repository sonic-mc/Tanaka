@extends('layouts.app')

@section('title', 'Discharged Patients')

@section('content')
<div class="container">
    <h1 class="mb-4">Discharged Patients</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-2">
            <input type="number" name="patient_id" value="{{ request('patient_id') }}" class="form-control" placeholder="Patient ID">
        </div>
        <div class="col-md-2">
            <input type="number" name="admission_id" value="{{ request('admission_id') }}" class="form-control" placeholder="Admission ID">
        </div>
        <div class="col-md-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" placeholder="From">
        </div>
        <div class="col-md-3">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" placeholder="To">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Admission</th>
                    <th>Discharge Date</th>
                    <th>Requires Follow-up</th>
                    <th>Discharged By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($discharges as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>
                        @if($d->patient)
                            {{ $d->patient->name ?? ('Patient #' . $d->patient_id) }}
                        @else
                            Patient #{{ $d->patient_id }}
                        @endif
                    </td>
                    <td>
                        @if($d->admission)
                            #{{ $d->admission_id }} ({{ $d->admission->admission_date?->format('Y-m-d') }})
                        @else
                            #{{ $d->admission_id }}
                        @endif
                    </td>
                    <td>{{ $d->discharge_date?->format('Y-m-d') }}</td>
                    <td>
                        @if($d->requires_follow_up)
                            <span class="badge bg-warning text-dark">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </td>
                    <td>{{ $d->dischargedBy->name ?? 'User #'.$d->discharged_by }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('discharges.show', $d) }}">View</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('discharges.edit', $d) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('discharges.destroy', $d) }}" onsubmit="return confirm('Delete this discharge record?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No discharges found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $discharges->links() }}
</div>
@endsection
