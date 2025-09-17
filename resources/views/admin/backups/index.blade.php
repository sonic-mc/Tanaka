@extends('layouts.app')

@section('header')
    System Backups
@endsection

@section('content')
<table class="table table-striped">
    <thead>
        <tr>
            <th>File Path</th>
            <th>Created By</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($backups as $backup)
        <tr>
            <td>{{ $backup->file_path }}</td>
            <td>{{ $backup->creator->name }}</td>
            <td>{{ $backup->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
