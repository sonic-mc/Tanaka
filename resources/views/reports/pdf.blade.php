<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 10px; }
        h2 { font-size: 16px; margin: 18px 0 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px; vertical-align: top; }
        th { background: #f6f6f6; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>Chimhanda Psych Care Reports</h1>
    @if(!empty($patient))
        <p><strong>Patient:</strong> {{ $patient->full_name }}</p>
    @endif

    @foreach($data as $key => $items)
        <h2>{{ ucwords(str_replace('_', ' ', $key)) }}</h2>
        @php
            $isCollection = $items instanceof \Illuminate\Support\Collection;
            $isModel = $items instanceof \Illuminate\Database\Eloquent\Model;
        @endphp

        @if($isCollection && $items->isNotEmpty())
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

                                    $colLower = strtolower($col);
                                    if (str_contains($colLower, 'created_at') || str_contains($colLower, 'updated_at') || str_contains($colLower, 'date') || str_contains($colLower, 'at')) {
                                        try { $val = $val ? \Carbon\Carbon::parse($val)->format('d M Y H:i') : $val; } catch (\Exception $e) {}
                                    }

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
        @elseif($isModel)
            @php $attributes = $items->getAttributes(); @endphp
            <table>
                <tbody>
                    @foreach($attributes as $col => $val)
                        @php
                            $display = $val;

                            $colLower = strtolower($col);
                            if (str_contains($colLower, 'created_at') || str_contains($colLower, 'updated_at') || str_contains($colLower, 'date') || str_contains($colLower, 'at')) {
                                try { $display = $display ? \Carbon\Carbon::parse($display)->format('d M Y H:i') : $display; } catch (\Exception $e) {}
                            }

                            if (in_array($col, ['amount','balance_due','total_amount','outstanding_balance'], true) && $display !== null) {
                                $display = number_format((float)$display, 2);
                            }
                        @endphp
                        <tr>
                            <th style="width: 30%">{{ ucwords(str_replace('_',' ',$col)) }}</th>
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
        @else
            <p class="muted">No records found for {{ ucwords(str_replace('_', ' ', $key)) }}.</p>
        @endif
    @endforeach
</body>
</html>
