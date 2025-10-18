<div class="col-lg-6 mb-4">
    <div class="card modern-card chart-card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="chart-title mb-0">Notifications</h5>
            @if(isset($allowMarkAll) && $allowMarkAll)
                <form action="{{ route('dashboard.notifications.markAll') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Mark all as read</button>
                </form>
            @endif
        </div>

        <div class="mt-3">
            @forelse($notifications as $n)
                <div class="activity-item d-flex align-items-center mb-3">
                    <i class="fas {{ $n['icon'] }} {{ $n['color'] }} me-3"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">
                            @if(!empty($n['link']))
                                <a href="{{ $n['link'] }}" class="text-decoration-none">{{ $n['title'] }}</a>
                            @else
                                {{ $n['title'] }}
                            @endif
                            @if(!empty($n['unread']))
                                <span class="badge bg-danger ms-2">New</span>
                            @endif
                        </div>
                        @if(!empty($n['subtitle']))
                            <small class="text-muted d-block">{{ $n['subtitle'] }}</small>
                        @endif
                        @if(!empty($n['time']))
                            <small class="text-muted">{{ \Carbon\Carbon::parse($n['time'])->diffForHumans() }}</small>
                        @endif
                    </div>

                    @if(!empty($n['id']))
                        <form action="{{ route('dashboard.notifications.read', $n['id']) }}" method="POST" class="ms-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Mark as read</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="text-muted">No notifications yet.</div>
            @endforelse
        </div>
    </div>
</div>
