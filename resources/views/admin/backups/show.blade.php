@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary">Backup Details</h2>
    <a href="{{ route('admin.backups.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div><strong>Filename:</strong> <code>{{ $backup->filename ?? basename($backup->file_path) }}</code></div>
                <div><strong>Type:</strong> {{ ucfirst($backup->type) }}</div>
                <div><strong>Status:</strong>
                    <span class="badge 
                        @if($backup->status === 'completed') bg-success
                        @elseif($backup->status === 'failed') bg-danger
                        @elseif($backup->status === 'restored') bg-warning text-dark
                        @elseif($backup->status === 'pending') bg-info
                        @else bg-secondary
                        @endif">
                        {{ ucfirst($backup->status) }}
                    </span>
                </div>
                <div><strong>Created At:</strong> {{ $backup->created_at?->format('d M Y H:i') }}</div>
                <div><strong>Restored At:</strong> {{ $backup->restored_at?->format('d M Y H:i') ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div><strong>Created By:</strong>
                    @if($backup->creator)
                        {{ $backup->creator->name }} <small class="text-muted">&lt;{{ $backup->creator->email }}&gt;</small>
                    @else
                        <span class="text-muted">System</span>
                    @endif
                </div>
                <div><strong>Origin IP:</strong> {{ $backup->origin_ip ?? '—' }}</div>
                <div><strong>Size:</strong> {{ isset($size) && $size ? number_format($size/1024/1024, 2) . ' MB' : '—' }}</div>
                <div><strong>Checksum (SHA-256):</strong> <code>{{ $backup->checksum_sha256 ?? '—' }}</code></div>
                <div><strong>Notes:</strong> {{ $backup->notes ?? '—' }}</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if($exists)
                <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-outline-primary">
                    <i class="bi bi-download"></i> Download
                </a>
            @endif
            <form method="POST" action="{{ route('admin.backups.restore', $backup) }}"
                onsubmit="return confirm('Restore from this backup? This action will overwrite current state.')">
                @csrf
                <button class="btn btn-outline-warning">
                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                </button>
            </form>
            <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}"
                onsubmit="return confirm('Delete this backup permanently?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
