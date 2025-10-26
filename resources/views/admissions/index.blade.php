@extends('layouts.app')

@section('title', 'Admissions')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Admissions</h3>
        <a href="{{ route('admissions.create') }}" class="btn btn-primary">New Admission</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Patient Code</th>
                            <th>Admission Date</th>
                            <th>Room</th>
                            <th>Care Level</th>
                            <th>Status</th>
                            <th>Admitted By</th>
                            <th style="width:180px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admissions as $ad)
                            <tr>
                                <td>{{ $ad->patient->first_name ?? '' }} {{ $ad->patient->last_name ?? '' }}</td>
                                <td>{{ $ad->patient->patient_code ?? '' }}</td>
                                <td>
                                    {{ $ad->admission_date
                                        ? \Illuminate\Support\Carbon::parse($ad->admission_date)->format('Y-m-d')
                                        : '—' }}
                                </td>
                                <td>{{ $ad->room_number ?? '—' }}</td>
                                <td>{{ $ad->careLevel->name ?? ($ad->care_level_id ?? '—') }}</td>
                                <td>
                                    <span class="badge bg-{{ $ad->status === 'active' ? 'success' : ($ad->status === 'discharged' ? 'secondary' : 'warning') }}">
                                        {{ ucfirst($ad->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($ad->admittedBy)->name ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('admissions.show', $ad) }}" class="btn btn-sm btn-outline-info">View</a>
                                
                                    @if(auth()->user()->role === 'psychiatrist')
                                        <a href="{{ route('admissions.edit', $ad) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form action="{{ route('admissions.destroy', $ad) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Delete this admission?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                                
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-3">No admissions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $admissions->links() }}
    </div>
</div>
@endsection
