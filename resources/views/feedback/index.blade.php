@extends('layouts.app')

@section('header', 'Feedback (Admin)')

@section('content')
<div class="container">
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search subject, message, or user" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive card shadow-sm">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Subject</th>
                    <th>Rating</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
            @forelse($feedback as $item)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $item->user_name ?? 'Unknown' }}</td>
                    <td>{{ $item->subject }}</td>
                    <td>
                        @if($item->rating)
                            {{ $item->rating }} {{ $item->rating == 1 ? 'Star' : 'Stars' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($item->message, 120) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No feedback found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $feedback->links() }}
    </div>
</div>
@endsection
