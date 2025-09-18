@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'Profile Overview')

@section('content')
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">
                    <i class="bi bi-person-lines-fill me-1"></i> Personal Info
                </button>
            </li>
            <li class="nav-item ms-auto">
                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit Profile
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body tab-content" id="profileTabsContent">
        <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
            <dl class="row mb-0">
                <dt class="col-sm-3"><i class="bi bi-person-fill me-2 text-primary"></i> Full Name</dt>
                <dd class="col-sm-9">{{ $user->name }}</dd>

                <dt class="col-sm-3"><i class="bi bi-envelope-fill me-2 text-primary"></i> Email Address</dt>
                <dd class="col-sm-9">{{ $user->email }}</dd>

                <dt class="col-sm-3"><i class="bi bi-telephone-fill me-2 text-primary"></i> Phone Number</dt>
                <dd class="col-sm-9">{{ $user->phone ?? '—' }}</dd>

                <dt class="col-sm-3"><i class="bi bi-person-badge-fill me-2 text-primary"></i> Role</dt>
                <dd class="col-sm-9 text-capitalize">{{ $user->role ?? '—' }}</dd>

                <dt class="col-sm-3"><i class="bi bi-calendar-check-fill me-2 text-primary"></i> Joined</dt>
                <dd class="col-sm-9">{{ $user->created_at->format('d M Y') }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
