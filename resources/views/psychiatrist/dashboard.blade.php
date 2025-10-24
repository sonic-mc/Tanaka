@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        
        <div>
            <h2 class="fw-bold text-dark mb-1">Psychiatrist Dashboard</h2>
            <p class="text-muted mb-0">Monitor patients, therapy sessions, and mental health operations</p>
            @if($unevaluatedCount > 0)
        <div class="alert alert-warning d-flex align-items-center">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>{{ $unevaluatedCount }}</strong> new patient{{ $unevaluatedCount > 1 ? 's' : '' }} awaiting evaluation.
            <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-dark ms-3">View Patients</a>
        </div>
        @else
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle me-2"></i> No new patients awaiting evaluation.
            </div>
        @endif

        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                <i class="fas fa-calendar-alt me-2"></i>{{ date('M d, Y') }}
            </span>
            <button class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-download me-2"></i>Export Report
            </button>
        </div>
    </div>
@endsection

@section('content')
<style>
    :root {
        --primary-blue: #4A90F2;
        --secondary-blue: #357ABD;
        --dark-blue: #2C3E50;
        --light-blue: #E3F2FD;
        --accent-blue: #1976D2;
        --success-green: #4CAF50;
        --warning-orange: #FF9800;
        --danger-red: #F44336;
        --light-gray: #F8F9FA;
        --medium-gray: #6C757D;
    }

    .modern-card {
        background: #ffffff;
        border: none;
        border-radius: 20px;
        box-shadow: 0 6px 24px rgba(74, 144, 242, 0.12);
        transition: all 0.3s ease;
    }

    .modern-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 36px rgba(74, 144, 242, 0.2);
    }

    .stats-card {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: #fff;
        position: relative;
        overflow: hidden;
    }

    .stats-number {
        font-size: 2.4rem;
        font-weight: 700;
    }

    .stats-label {
        font-size: 1rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .chart-card {
        padding: 1.25rem;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .chart-wrapper {
        flex: 1;
        position: relative;
        height: 260px;
    }

    .chart-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: var(--dark-blue);
        margin-bottom: 1rem;
    }

    .metric-card {
        background: var(--light-gray);
        border-radius: 16px;
        padding: 1.25rem;
        text-align: center;
        transition: 0.3s;
    }

    .metric-card:hover {
        background: var(--light-blue);
    }

    .metric-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--dark-blue);
    }

    .metric-label {
        color: var(--medium-gray);
        font-weight: 500;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-blue);
        margin-bottom: .25rem;
    }

    .section-subtitle {
        color: var(--medium-gray);
        font-size: 0.95rem;
    }
</style>

<!-- KPIs -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="section-title">Key Metrics</h3>
        <p class="section-subtitle">Quick overview of your psychiatry practice</p>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100 p-4">
            <div class="stats-number">{{ $admissionsCount }}</div>
            <div class="stats-label">Active Patients</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100 p-4">
            <div class="stats-number">{{ $therapySessionCount }}</div>
            <div class="stats-label">Therapy Sessions</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100 p-4">
            <div class="stats-number">{{ $evaluationCount }}</div>
            <div class="stats-label">Evaluations</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100 p-4">
            <div class="stats-number">{{ $incidentsCount }}</div>
            <div class="stats-label">Incidents</div>
        </div>
    </div>
</div>

<!-- Therapy & Progress -->
<div class="row mb-5">
    <div class="col-lg-8 mb-4">
        <div class="card modern-card chart-card">
            <h5 class="chart-title">Therapy Attendance Trends</h5>
            <div class="chart-wrapper mt-2">
                <canvas id="therapyChart"></canvas>
            </div>
        </div>
    </div>

    @include('psychiatrist.partials.progress_distribution', ['progressDistribution' => $progressDistribution])
</div>

<!-- Notifications & Decision Support -->
<div class="row mb-5">
    @include('dashboard.partials.notifications', ['notifications' => $notifications, 'allowMarkAll' => true])

    <div class="col-lg-6 mb-4">
        <div class="card modern-card chart-card">
            <h5 class="chart-title">Decision Support</h5>
            <p class="text-muted">AI-assisted recommendations based on patient history and symptoms</p>
            <ul class="list-unstyled">
                <li><i class="bi bi-lightbulb-fill text-primary me-2"></i>Consider medication adjustment for 3 patients</li>
                <li><i class="bi bi-lightbulb-fill text-primary me-2"></i>Follow-up therapy suggested for anxiety group</li>
                <li><i class="bi bi-lightbulb-fill text-primary me-2"></i>2 patients flagged for risk assessment</li>
            </ul>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Therapy Attendance Trends
    new Chart(document.getElementById('therapyChart'), {
        type: 'line',
        data: {
            labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
            datasets: [{
                label: 'Sessions Held',
                data: [10, 12, 9, 14, 11, 8, 13],
                borderColor: '#4A90F2',
                backgroundColor: 'rgba(74,144,242,0.1)',
                fill: true,
                tension: 0.4
            },{
                label: 'Sessions Missed',
                data: [2, 1, 3, 2, 2, 1, 0],
                borderColor: '#F44336',
                backgroundColor: 'rgba(244,67,54,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Patient Progress Distribution
    new Chart(document.getElementById('progressChart'), {
        type: 'doughnut',
        data: {
            labels: ['Improved','Stable','Declined'],
            datasets: [{
                data: [40,45,15],
                backgroundColor: ['#4CAF50','#FF9800','#F44336'],
                cutout: '65%'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display:false } } }
    });
});
</script>
@endsection
