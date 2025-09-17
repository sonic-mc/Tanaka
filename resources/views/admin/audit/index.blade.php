@extends('layouts.app')

@section('header')
    Audit Logs
@endsection

@section('content')
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>User</th>
            <th>Action</th>
            <th>Description</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>{{ $log->user->name ?? 'System' }}</td>
            <td>{{ $log->action }}</td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->timestamp }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
