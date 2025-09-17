@extends('layouts.app')

@section('header')
    Staff Tasks
@endsection

@section('content')
<table class="table table-striped">
    <thead>
        <tr>
            <th>Title</th>
            <th>Assigned To</th>
            <th>Status</th>
            <th>Due</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tasks as $task)
        <tr>
            <td>{{ $task->title }}</td>
            <td>{{ $task->assignee->name }}</td>
            <td><span class="badge bg-warning">{{ ucfirst($task->status) }}</span></td>
            <td>{{ $task->due_date }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
