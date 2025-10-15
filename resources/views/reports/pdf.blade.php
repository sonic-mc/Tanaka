<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .muted { color: #777; font-style: italic; }
    </style>
</head>
<body>
    <h1>Module Reports</h1>

    @if($patientId)
        <p><strong>Patient:</strong>
            {{ \App\Models\Patient::find($patientId)->first_name ?? '' }}
            {{ \App\Models\Patient::find($patientId)->last_name ?? '' }}
        </p>
    @endif

    @foreach($data as $key => $items)
        <h2>{{ ucwords(str_replace('_', ' ', $key)) }}</h2>

        @if($items instanceof \Illuminate\Support\Collection && $items->isNotEmpty())
            <table>
                <thead>
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
                                        {{ json_encode($val) }}
                                    @else
                                        {{ $val ?: '—' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">No records found for {{ $key }}.</p>
        @endif
    @endforeach
</body>
</html>
