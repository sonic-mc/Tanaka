@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Nurse Assignments</h3>
        <div class="text-muted">Manage and review nurse-to-patient assignments</div>
    </div>
    <a href="{{ route('nurse-assignments.create') }}" class="btn btn-primary">
        <i class="bi bi-person-check me-1"></i> Assign Patient
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-3">
                <select name="nurse_id" class="form-select">
                    <option value="">All Nurses</option>
                    @foreach($nurses as $n)
                        <option value="{{ $n->id }}" @selected(request('nurse_id')==$n->id)>{{ $n->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                @php $sh = request('shift') @endphp
                <select name="shift" class="form-select">
                    <option value="">All Shifts</option>
                    <option value="morning" @selected($sh==='morning')>Morning</option>
                    <option value="evening" @selected($sh==='evening')>Evening</option>
                    <option value="night" @selected($sh==='night')>Night</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="From">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="To">
            </div>
            <div class="col-6 col-md-2">
                <input type="text" name="q" class="form-control" placeholder="Search patient" value="{{ request('q') }}">
            </div>
            <div class="col-6 col-md-1 d-grid">
                <button class="btn btn-outline-secondary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nurse</th>
                    <th>Patient</th>
                    <th>Room</th>
                    <th>Shift</th>
                    <th>Assigned Date</th>
                    <th>Notes</th>
                    <th class="text-end" style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $a->nurse?->name }}</div>
                            <div class="text-muted small">Assigned by: {{ $a->assignedBy?->name ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">
                                {{ $a->admission?->patient?->first_name }} {{ $a->admission?->patient?->last_name }}
                            </div>
                            <div class="text-muted small">{{ $a->admission?->patient?->patient_code }}</div>
                        </td>
                        <td>{{ $a->admission?->room_number ?? '—' }}</td>
                        <td>
                            @if($a->shift)
                                <span class="badge text-bg-{{ $a->shift === 'morning' ? 'success' : ($a->shift === 'evening' ? 'primary' : 'dark') }}">
                                    {{ ucfirst($a->shift) }}
                                </span>
                            @else
                                <span class="badge text-bg-secondary">N/A</span>
                            @endif
                        </td>
                        <td>{{ optional($a->assigned_date)->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-truncate" style="max-width: 240px;" title="{{ $a->notes }}">{{ $a->notes }}</td>
                        <td class="text-end">
                            <a href="{{ route('nurse-assignments.edit', $a->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil-square"></i> Reassign
                            </a>
                            <form action="{{ route('nurse-assignments.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Unassign this nurse from the patient?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-circle"></i> Unassign
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No assignments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($assignments->hasPages())
        <div class="card-footer">
            {{ $assignments->links() }}
        </div>
    @endif
</div>
@endsection
