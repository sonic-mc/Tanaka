@extends('layouts.app')

@section('content')
<div class="welcome-container">
    <h1>Welcome to Psych Monitor</h1>
    <p class="intro-text">
        Your account has been successfully created. You now have access to a secure, role-based system for managing psychiatric patient admissions, evaluations, and progress reports.
    </p>

    <div class="role-info">
        <h2>Your Role:</h2>
        <p>
            Use the navigation bar to access your dashboard and begin working with patients, evaluations, or reports based on your assigned permissions.
        </p>
    </div>

    <div class="next-steps">
        <h3>Next Steps</h3>
        <ul>
            <li>📋 Review your dashboard for pending tasks</li>
            <li>👥 Add or view patient records</li>
            <li>🧠 Begin initial evaluations</li>
            <li>📈 Monitor progress and generate reports</li>
        </ul>
    </div>
</div>
@endsection
