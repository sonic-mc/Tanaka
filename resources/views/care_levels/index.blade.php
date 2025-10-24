@extends('layouts.app')

@section('title', 'Care Levels')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Care Levels</h3>
        <a href="{{ route('care_levels.create') }}" class="btn btn-primary">Add Care Level</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th style="width:200px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levels as $level)
                            <tr>
                                <td>{{ $level->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($level->description ?? '—', 140) }}</td>
                                <td>
                                    <a href="{{ route('care_levels.show', $level) }}" class="btn btn-sm btn-outline-info">View</a>
                                    <a href="{{ route('care_levels.edit', $level) }}" class="btn btn-sm btn-outline-warning">Edit</a>

                                    <form action="{{ route('care_levels.destroy', $level) }}" method="POST" class="d-inline-block ms-1" onsubmit="return confirm('Delete this care level?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3">No care levels found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
