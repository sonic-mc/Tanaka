@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Module Reports')

@section('content')
<div class="container">

    {{-- Filter form --}}
    <form method="GET" action="{{ route('reports.index') }}" class="mb-4">
        @php
            $selectedModules = request('modules', []);
            $allModules = $allowedModules ?? [
                'patients','admissions','discharges','evaluations','incident_reports',
                'progress_reports','billing_statements','therapy_sessions','invoices','payments'
            ];
        @endphp

        <div class="row mb-3">
            <div class="col-md-8">
                <label class="form-label fw-bold">Select Modules:</label>
                <div class="row g-2">
                    @foreach($allModules as $module)
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="modules[]" value="{{ $module }}"
                                       id="{{ $module }}"
                                       {{ in_array($module, $selectedModules, true) ? 'checked' : '' }}>
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
                    @foreach(\App\Models\PatientDetail::select('id','first_name','middle_name','last_name')->orderBy('last_name')->get() as $p)
                        <option value="{{ $p->id }}" {{ (string)request('patient_id') === (string)$p->id ? 'selected' : '' }}>
                            {{ trim($p->first_name.' '.($p->middle_name ? $p->middle_name.' ' : '').$p->last_name) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <button class="btn btn-primary">
            <i class="bi bi-search"></i> View Reports
        </button>
    </form>

    @if(!empty($modules))
        {{-- Export --}}
        <form method="POST" action="{{ route('reports.export') }}" class="mb-4">
            @csrf
            @foreach($modules as $module)
                <input type="hidden" name="modules[]" value="{{ $module }}">
            @endforeach
            <input type="hidden" name="patient_id" value="{{ request('patient_id') }}">

            @if(!empty($patient))
                <p class="mb-2">
                    Exporting reports for:
                    <strong>{{ $patient->full_name }}</strong>
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
                    @php
                        $renderCollection = $items instanceof \Illuminate\Support\Collection;
                        $renderModel = $items instanceof \Illuminate\Database\Eloquent\Model;
                    @endphp

                    @if($renderCollection && $items->isNotEmpty())
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
                                                    if ($col === 'patient_id' && method_exists($row, 'patient') && $row->relationLoaded('patient') && $row->patient) {
                                                        $val = $row->patient->full_name ?? trim(($row->patient->first_name ?? '').' '.($row->patient->last_name ?? ''));
                                                    }
                                                    if ($col === 'psychiatrist_id' && method_exists($row, 'psychiatrist') && $row->relationLoaded('psychiatrist') && $row->psychiatrist) {
                                                        $val = $row->psychiatrist->name ?? $row->psychiatrist->email ?? $row->psychiatrist->id;
                                                    }
                                                    if ($col === 'clinician_id' && method_exists($row, 'clinician') && $row->relationLoaded('clinician') && $row->clinician) {
                                                        $val = $row->clinician->name ?? $row->clinician->email ?? $row->clinician->id;
                                                    }
                                                    if ($col === 'discharged_by' && method_exists($row, 'dischargedBy') && $row->relationLoaded('dischargedBy') && $row->dischargedBy) {
                                                        $val = $row->dischargedBy->name ?? $row->dischargedBy->email ?? $row->dischargedBy->id;
                                                    }
                                                    if ($col === 'received_by' && method_exists($row, 'receiver') && $row->relationLoaded('receiver') && $row->receiver) {
                                                        $val = $row->receiver->name ?? $row->receiver->email ?? $row->receiver->id;
                                                    }
                                                    if ($col === 'invoice_id' && method_exists($row, 'invoice') && $row->relationLoaded('invoice') && $row->invoice) {
                                                        $val = $row->invoice->invoice_number ?? ('#'.$row->invoice->id);
                                                    }
                                                    if ($col === 'admission_id' && method_exists($row, 'admission') && $row->relationLoaded('admission') && $row->admission) {
                                                        $val = 'Admission #'.$row->admission->id;
                                                    }
                                                    if ($col === 'evaluation_id' && method_exists($row, 'evaluation') && $row->relationLoaded('evaluation') && $row->evaluation) {
                                                        $val = 'Evaluation #'.$row->evaluation->id;
                                                    }
                                                    if ($col === 'care_level_id' && method_exists($row, 'careLevel') && $row->relationLoaded('careLevel') && $row->careLevel) {
                                                        $val = $row->careLevel->name ?? ('#'.$row->careLevel->id);
                                                    }
                                                    if ($col === 'created_by' && method_exists($row, 'creator') && $row->relationLoaded('creator') && $row->creator) {
                                                        $val = $row->creator->name ?? $row->creator->email ?? $row->creator->id;
                                                    }
                                                    if ($col === 'last_modified_by' && method_exists($row, 'lastModifier') && $row->relationLoaded('lastModifier') && $row->lastModifier) {
                                                        $val = $row->lastModifier->name ?? $row->lastModifier->email ?? $row->lastModifier->id;
                                                    }

                                                    // Format dates/times
                                                    $colLower = strtolower($col);
                                                    if (str_contains($colLower, 'created_at') || str_contains($colLower, 'updated_at') || str_contains($colLower, 'date') || str_contains($colLower, 'at')) {
                                                        try {
                                                            $val = $val ? \Carbon\Carbon::parse($val)->format('d M Y H:i') : $val;
                                                        } catch (\Exception $e) {
                                                            // leave as-is
                                                        }
                                                    }

                                                    // Money formatting for common columns
                                                    if (in_array($col, ['amount','balance_due','total_amount','outstanding_balance'], true) && $val !== null) {
                                                        $val = number_format((float)$val, 2);
                                                    }
                                                @endphp
                                                <td>
                                                    @if(is_array($val) || is_object($val))
                                                        <pre class="m-0 small">{{ json_encode($val, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        {{ $val !== null && $val !== '' ? $val : '—' }}
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($renderModel)
                        @php
                            $model = $items;
                            $attributes = $model->getAttributes();
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <tbody>
                                    @foreach($attributes as $col => $val)
                                        @php
                                            $display = $val;

                                            if ($col === 'created_by' && method_exists($model, 'creator') && $model->relationLoaded('creator') && $model->creator) {
                                                $display = $model->creator->name ?? $model->creator->email ?? $model->creator->id;
                                            }
                                            if ($col === 'last_modified_by' && method_exists($model, 'lastModifier') && $model->relationLoaded('lastModifier') && $model->lastModifier) {
                                                $display = $model->lastModifier->name ?? $model->lastModifier->email ?? $model->lastModifier->id;
                                            }

                                            $colLower = strtolower($col);
                                            if (str_contains($colLower, 'created_at') || str_contains($colLower, 'updated_at') || str_contains($colLower, 'date') || str_contains($colLower, 'at')) {
                                                try {
                                                    $display = $display ? \Carbon\Carbon::parse($display)->format('d M Y H:i') : $display;
                                                } catch (\Exception $e) {}
                                            }
                                        @endphp
                                        <tr>
                                            <th style="width: 25%">{{ ucwords(str_replace('_',' ',$col)) }}</th>
                                            <td>
                                                @if(is_array($display) || is_object($display))
                                                    <pre class="m-0 small">{{ json_encode($display, JSON_PRETTY_PRINT) }}</pre>
                                                @else
                                                    {{ $display !== null && $display !== '' ? $display : '—' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No records found for {{ ucwords(str_replace('_', ' ', $key)) }}.</p>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
