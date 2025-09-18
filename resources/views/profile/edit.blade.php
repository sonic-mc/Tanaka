@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i> Update Your Profile</h5>
    </div>

    <div class="card-body">
        @if(session('status') === 'profile-updated')
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i> Profile updated successfully.
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label"><i class="bi bi-person-fill me-1"></i> Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><i class="bi bi-envelope-fill me-1"></i> Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @if($user->email_verified_at)
                    <small class="text-success"><i class="bi bi-patch-check-fill me-1"></i> Verified on {{ $user->email_verified_at->format('d M Y') }}</small>
                @else
                    <small class="text-warning"><i class="bi bi-exclamation-circle-fill me-1"></i> Not verified</small>
                @endif
            </div>

            <div class="mb-3">
                <label for="role" class="form-label"><i class="bi bi-person-badge-fill me-1"></i> Role</label>
                <select id="role" name="role" class="form-select" required>
                    @foreach(['admin', 'psychiatrist', 'nurse'] as $roleOption)
                        <option value="{{ $roleOption }}" {{ old('role', $user->role) === $roleOption ? 'selected' : '' }}>
                            {{ ucfirst($roleOption) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Changes
            </button>
        </form>

        <hr class="my-4">

        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('DELETE')

            <div class="mb-3">
                <label for="password" class="form-label"><i class="bi bi-lock-fill me-1"></i> Confirm Password to Delete Account</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash-fill me-1"></i> Delete Account
            </button>
        </form>
    </div>
</div>
@endsection


<style>
    body {
        padding-top: 70px; /* Height of fixed navbar */
    }

    .fixed-sidebar {
        position: fixed;
        top: 70px; /* Below navbar */
        left: 0;
        height: calc(100vh - 70px);
        width: 250px;
        overflow-y: auto;
        background-color: rgba(13, 110, 253, 0.85);
        backdrop-filter: blur(10px);
        border-right: 1px solid rgba(255, 255, 255, 0.2);
        z-index: 1020;
    }

    .main-content {
        margin-left: 250px;
    }

    .animate-navbar {
        animation: slideDown 0.4s ease-out;
        z-index: 1030;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .nav-link.active {
        font-weight: bold;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }

    .calendar-container {
        max-width: 400px;
        margin: 0 auto;
    }

    /* 3D Flip Animation Styles */
    .perspective {
        perspective: 1000px;
        position: relative;
    }

    .card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.6s;
        transform-style: preserve-3d;
    }

    .card-inner.flipped {
        transform: rotateY(180deg);
    }

    .card-face {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 0.375rem;
        background: white;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-back {
        transform: rotateY(180deg);
    }

    /* Calendar Styles */
    .calendar-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        padding: 1rem;
        border-radius: 0.375rem 0.375rem 0 0;
        margin: -1rem -1rem 1rem -1rem;
    }

    .calendar-nav {
        cursor: pointer;
        font-size: 1.25rem;
        transition: all 0.2s ease;
        padding: 0.25rem 0.5rem;
        border-radius: 50%;
    }

    .calendar-nav:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.1);
    }

    .day-header {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        padding: 0.5rem 0;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: 0.375rem;
        margin: 2px;
        font-weight: 500;
        position: relative;
    }

    .calendar-day:hover:not(.empty):not(.today) {
        background: #f3f4f6;
        transform: scale(1.05);
    }

    .calendar-day.today {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.4);
    }

    .calendar-day.other-month {
        color: #d1d5db;
    }

    .calendar-day.has-event {
        position: relative;
    }

    .calendar-day.has-event::after {
        content: '';
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        width: 6px;
        height: 6px;
        background: #ef4444;
        border-radius: 50%;
    }

    .calendar-day.today.has-event::after {
        background: white;
    }

    /* Month transition animation */
    .month-transition {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Quick Actions */
    .quick-actions {
        margin-top: 1.5rem;
    }

    .quick-action-btn {
        background: white;
        border: 1px solid #e5e7eb;
        color: #374151;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .quick-action-btn:hover {
        background: #f9fafb;
        border-color: #4f46e5;
        color: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .events-indicator {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: #ef4444;
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-weight: 600;
    }
</style>