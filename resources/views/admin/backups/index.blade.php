@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary">Data Backup & Restore</h2>
    <span class="text-muted">Safeguard system data and restore when needed</span>
</div>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        {{-- Trigger Backup --}}
        <form method="POST" action="{{ route('admin.backups.store') }}" class="mb-4">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="type" class="form-label">Backup Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="full">Full</option>
                        <option value="database">Database Only</option>
                        <option value="files">Files Only</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="notes" class="form-label">Notes (optional)</label>
                    <input type="text" name="notes" id="notes" class="form-control" placeholder="Reason or context for backup">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-success w-100">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> Create Backup
                    </button>
                </div>
            </div>
        </form>

        {{-- Backup History --}}
        @if($backups->count())
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Filename</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Restored At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr>
                        <td><code>{{ $backup->filename ?? basename($backup->file_path) }}</code></td>
                        <td><span class="badge bg-secondary">{{ ucfirst($backup->type) }}</span></td>
                        <td>
                            <span class="badge 
                                @if($backup->status === 'completed') bg-success
                                @elseif($backup->status === 'failed') bg-danger
                                @elseif($backup->status === 'restored') bg-warning text-dark
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($backup->status) }}
                            </span>
                        </td>
                        <td>
                            @if($backup->creator)
                                <span class="fw-semibold">{{ $backup->creator->name }}</span><br>
                                <small class="text-muted">{{ $backup->creator->email }}</small>
                            @else
                                <span class="text-muted">System</span>
                            @endif
                            <td>{{ \Carbon\Carbon::parse($backup->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                {{ $backup->restored_at 
                                    ? \Carbon\Carbon::parse($backup->restored_at)->format('d M Y H:i') 
                                    : '—' 
                                }}
                            </td>
                            
                            <form method="POST" action="{{ route('admin.backups.restore', $backup->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning" onclick="return confirm('Restore from this backup?')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $backups->links() }}
        </div>
        @else
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle me-2"></i> No backups found. Create one to begin tracking.
            </div>
        @endif
    </div>
</div>
@endsection
