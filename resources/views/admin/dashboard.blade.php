@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Admin Dashboard</h2>
            <p class="text-muted mb-0">Monitor and manage your healthcare facility operations</p>
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
        height: 260px; /* uniform chart height */
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
         {{-- Popup alert --}}
            @if($noRoleCount > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>{{ $noRoleCount }}</strong> user{{ $noRoleCount > 1 ? 's' : '' }} found without assigned roles.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @else
            <div class="alert alert-success">
                All users have roles assigned.
            </div>
        @endif
        <h3 class="section-title">Key Performance Indicators</h3>
        <p class="section-subtitle">Real-time overview of your healthcare facility metrics</p>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100 p-4">
            <div class="stats-number">{{ $patientCount }}</div>
            <div class="stats-label">Total Patients</div>
            <div class="small">+12% vs last month</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100 p-4">
            <div class="stats-number">{{ $staffCount }}</div>
            <div class="stats-label">Active Staff</div>
            <div class="small">+5% vs last month</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100 p-4">
            <div class="stats-number">{{ $pendingTasks }}</div>
            <div class="stats-label">Pending Tasks</div>
            <div class="small">-8% vs yesterday</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100 p-4">
            <div class="stats-number">{{ $criticalIncidents }}</div>
            <div class="stats-label">Critical Incidents</div>
            <div class="small">Under control</div>
        </div>
    </div>
</div>

<!-- Trends & Care Distribution -->
<div class="row mb-5">
    <div class="col-lg-8 mb-4">
        <div class="card modern-card chart-card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="chart-title">Patient Activity Trends</h5>
                <div class="btn-group btn-group-sm">
                    <input type="radio" class="btn-check" name="period" id="week" checked>
                    <label class="btn btn-outline-primary" for="week">7D</label>
                    <input type="radio" class="btn-check" name="period" id="month">
                    <label class="btn btn-outline-primary" for="month">1M</label>
                    <input type="radio" class="btn-check" name="period" id="year">
                    <label class="btn btn-outline-primary" for="year">1Y</label>
                </div>
            </div>
            <div class="chart-wrapper mt-2">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card modern-card chart-card">
            <h5 class="chart-title">Care Level Distribution</h5>
            <div class="chart-wrapper d-flex justify-content-center">
                <canvas id="careChart"></canvas>
            </div>
            <div class="row text-center mt-3">
                <div class="col-4">
                    <div class="metric-value text-primary">45%</div>
                    <div class="metric-label">Intensive</div>
                </div>
                <div class="col-4">
                    <div class="metric-value text-success">35%</div>
                    <div class="metric-label">Standard</div>
                </div>
                <div class="col-4">
                    <div class="metric-value text-info">20%</div>
                    <div class="metric-label">Basic</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clinical Operations -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="section-title">Clinical Operations</h3>
        <p class="section-subtitle">Monitor therapy sessions, evaluations, and patient progress</p>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-value">{{ $therapySessionCount }}</div>
            <div class="metric-label">Therapy Sessions</div>
            <div class="small">24 scheduled today</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            {{-- <div class="metric-value">{{ $evaluationCount }}</div> --}}
            <div class="metric-label">Evaluations</div>
            <div class="small">3 pending review</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-value">{{ $progressReportCount }}</div>
            <div class="metric-label">Progress Reports</div>
            <div class="small">12 due this week</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-value">{{ $dischargeCount }}</div>
            <div class="metric-label">Discharges</div>
            <div class="small">5 scheduled today</div>
        </div>
    </div>
</div>

<!-- Financial & Activity -->
<!-- Financial & Activity -->
<div class="row mb-5">
    <div class="col-lg-6 mb-4">
        <div class="card modern-card chart-card">
            <h5 class="chart-title">Financial Overview</h5>
            <div class="chart-wrapper">
                <canvas id="financialChart"></canvas>
            </div>
            <div class="row mt-3 text-center">
                <div class="col-6">
                    <div class="metric-value text-primary">
                        ${{ number_format($totalRevenue, 2) }}
                    </div>
                    <div class="metric-label">Total Revenue</div>
                </div>
                <div class="col-6">
                    <div class="metric-value text-success">
                        {{ number_format($paymentCount) }}
                    </div>
                    <div class="metric-label">Payments Received</div>
                </div>
            </div>
        </div>
    </div>


@push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const labels   = @json($chart['labels']);
            const invoices = @json($chart['invoices']);
            const payments = @json($chart['payments']);

            const ctx = document.getElementById('financialChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Invoiced',
                            data: invoices,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, .25)',
                            borderWidth: 1.5,
                            borderRadius: 6,
                        },
                        {
                            type: 'line',
                            label: 'Payments',
                            data: payments,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, .25)',
                            borderWidth: 2,
                            tension: .3,
                            pointRadius: 3,
                            pointBackgroundColor: '#10b981',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const v = ctx.parsed.y ?? 0;
                                    return `${ctx.dataset.label}: $${v.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: false,
                            grid: { display: false }
                        },
                        y: {
                            stacked: false,
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => '$' + Number(value).toLocaleString()
                            }
                        }
                    }
                }
            });
        })();
    </script>
@endpush

    {{-- ... your other dashboard content ... --}}
@include('nurse.partials.recent-activity')
{{-- ... --}}
</div>

<!-- System Status -->
<div class="row">
    <div class="col-12">
        <h3 class="section-title">System Status</h3>
        <p class="section-subtitle">Monitor system health and performance metrics</p>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-value text-success">99.9%</div>
            <div class="metric-label">System Uptime</div>
            <small class="text-muted">All systems operational</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-value">2</div>
            <div class="metric-label">System Backups</div>
            <small class="text-muted">Last: 3 hours ago</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-value">78%</div>
            <div class="metric-label">Storage Used</div>
            <small class="text-muted">340GB available</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-value">3</div>
            <div class="metric-label">Security Logs</div>
            <small class="text-muted">All secure</small>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Patient Activity
    new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
            datasets: [{
                label: 'Admissions',
                data: [12,19,15,25,22,18,24],
                borderColor: '#4A90F2',
                backgroundColor: 'rgba(74,144,242,0.1)',
                fill: true,
                tension: 0.4
            },{
                label: 'Discharges',
                data: [8,15,12,18,16,14,20],
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76,175,80,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Care Distribution
    new Chart(document.getElementById('careChart'), {
        type: 'doughnut',
        data: {
            labels: ['Intensive','Standard','Basic'],
            datasets: [{
                data: [45,35,20],
                backgroundColor: ['#4A90F2','#4CAF50','#17A2B8'],
                cutout: '65%'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display:false } } }
    });

    // Financial Overview
    new Chart(document.getElementById('financialChart'), {
        type: 'bar',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun'],
            datasets: [{
                data: [65000,75000,80000,85000,78000,90000],
                backgroundColor: '#4A90F2',
                borderRadius: 8
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins:{ legend:{ display:false } } }
    });
});
</script>
@endsection
