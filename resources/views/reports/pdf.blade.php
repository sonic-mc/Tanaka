<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Selected Reports</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Selected Reports</h1>

    @foreach($data as $key => $items)
        <h2>{{ ucwords(str_replace('_', ' ', $key)) }}</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Created At</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->created_at->format('d M Y') }}</td>
                    <td>{{ $item->notes ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
