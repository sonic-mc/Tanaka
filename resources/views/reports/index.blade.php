@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Module Reports')

@section('content')
<div class="container">

    {{-- Filter form --}}
    <form method="GET" action="{{ route('reports.index') }}" class="mb-4">
        <div class="row mb-3">
            <div class="col-md-8">
                <label class="form-label fw-bold">Select Modules:</label>
                <div class="row g-2">
                    @foreach([
                        'patients','discharges','evaluations','incident_reports',
                        'progress_reports','therapy_sessions',
                        'invoices','payments'
                    ] as $module)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="modules[]" value="{{ $module }}" 
                                       id="{{ $module }}" 
                                       {{ in_array($module, request('modules', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $module }}">
                                    {{ ucwords(str_replace('_', ' ', $module)) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Filter by Patient (optional):</label>
                <select name="patient_id" class="form-select">
                    <option value="">-- All Patients --</option>
                    @foreach(\App\Models\Patient::select('id','first_name','last_name')->get() as $p)
                        <option value="{{ $p->id }}" {{ request('patient_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->first_name }} {{ $p->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <button class="btn btn-primary">
            <i class="bi bi-search"></i> View Reports
        </button>
    </form>

    @if($modules)
        {{-- Export --}}
        <form method="POST" action="{{ route('reports.export') }}" class="mb-4">
            @csrf
            @foreach($modules as $module)
                <input type="hidden" name="modules[]" value="{{ $module }}">
            @endforeach
            <input type="hidden" name="patient_id" value="{{ request('patient_id') }}">

            @if(request('patient_id'))
                <p class="mb-2">
                    Exporting reports for: 
                    {{ \App\Models\Patient::find(request('patient_id'))->first_name ?? '' }} 
                    {{ \App\Models\Patient::find(request('patient_id'))->last_name ?? '' }}
                </p>
            @endif

            <button class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </button>
        </form>

        {{-- Display module reports --}}
        @foreach($data as $key => $items)
            <div class="card mb-5 shadow-sm">
                <div class="card-header fw-bold text-uppercase bg-light">
                    {{ ucwords(str_replace('_', ' ', $key)) }}
                </div>
                <div class="card-body">
                    @if($items instanceof \Illuminate\Support\Collection && $items->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        @foreach(array_keys($items->first()->getAttributes()) as $col)
                                            <th>{{ ucwords(str_replace('_',' ',$col)) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $row)
                                        <tr>
                                            @foreach(array_keys($row->getAttributes()) as $col)
                                                @php
                                                    $val = $row->$col;

                                                    // Relationship substitutions
                                                    if($col === 'patient_id' && isset($row->patient)) {
                                                        $val = $row->patient->first_name . ' ' . $row->patient->last_name;
                                                    }
                                                    if($col === 'evaluated_by' && isset($row->evaluator)) {
                                                        $val = $row->evaluator->name;
                                                    }
                                                    if($col === 'admitted_by' && isset($row->admittedBy)) {
                                                        $val = $row->admittedBy->name;
                                                    }
                                                    if($col === 'assigned_nurse_id' && isset($row->assignedNurse)) {
                                                        $val = $row->assignedNurse->name;
                                                    }
                                                    if($col === 'current_care_level_id' && isset($row->careLevel)) {
                                                        $val = $row->careLevel->name;
                                                    }
                                                    if($col === 'reported_by' && isset($row->reporter)) {
                                                        $val = $row->reporter->name;
                                                    }

                                                    // Format timestamps
                                                    if(str_contains($col, 'created_at') || str_contains($col, 'updated_at') || str_contains($col, 'date')) {
                                                        try {
                                                            $val = \Carbon\Carbon::parse($val)->format('d M Y H:i');
                                                        } catch (\Exception $e) {
                                                            // leave as-is if not parsable
                                                        }
                                                    }
                                                @endphp
                                                <td>
                                                    @if(is_array($val) || is_object($val))
                                                        <pre class="m-0 small">{{ json_encode($val) }}</pre>
                                                    @else
                                                        {{ $val ?: '—' }}
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No records found for {{ $key }}.</p>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
