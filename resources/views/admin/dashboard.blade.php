@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Healthcare Dashboard</h2>
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
        border-radius: 24px;
        box-shadow: 0 8px 32px rgba(74, 144, 242, 0.12);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
    }

    .modern-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 48px rgba(74, 144, 242, 0.2);
    }

    .stats-card {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .stats-number {
        font-size: 2.8rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
        color: inherit;
    }

    .stats-label {
        font-size: 1rem;
        opacity: 0.9;
        font-weight: 500;
        margin-bottom: 0.75rem;
    }

    .stats-change {
        font-size: 0.85rem;
        opacity: 0.8;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .card-icon {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: rgba(255, 255, 255, 0.9);
    }

    .section-header {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark-blue);
        margin-bottom: 0.5rem;
        position: relative;
        padding-left: 20px;
    }

    .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        border-radius: 2px;
    }

    .section-subtitle {
        color: var(--medium-gray);
        font-size: 0.95rem;
        padding-left: 20px;
    }

    .chart-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(74, 144, 242, 0.08);
    }

    .chart-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--dark-blue);
        margin: 0;
    }

    .metric-card {
        background: var(--light-gray);
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        background: var(--light-blue);
        transform: translateY(-2px);
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        background: var(--primary-blue);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: white;
        font-size: 20px;
    }

    .metric-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark-blue);
        margin-bottom: 0.5rem;
    }

    .metric-label {
        color: var(--medium-gray);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .progress-ring {
        width: 80px;
        height: 80px;
    }

    .progress-ring circle {
        transition: stroke-dasharray 0.5s ease-in-out;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        transition: background-color 0.3s ease;
    }

    .activity-item:hover {
        background-color: var(--light-blue);
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 16px;
    }

    .btn-modern {
        border-radius: 12px;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
    }

    .btn-primary-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(74, 144, 242, 0.3);
    }
</style>

<!-- Key Metrics Row -->
<div class="row mb-5">
    <div class="col-12">
        <div class="section-header">
            <h3 class="section-title">Key Performance Indicators</h3>
            <p class="section-subtitle">Real-time overview of your healthcare facility metrics</p>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100">
            <div class="card-body p-4 position-relative">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-number">{{ $patientCount }}</div>
                <div class="stats-label">Total Patients</div>
                <div class="stats-change">
                    <i class="fas fa-arrow-up"></i>
                    <span>+12% vs last month</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100">
            <div class="card-body p-4 position-relative">
                <div class="card-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stats-number">{{ $staffCount }}</div>
                <div class="stats-label">Active Staff</div>
                <div class="stats-change">
                    <i class="fas fa-arrow-up"></i>
                    <span>+5% vs last month</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100">
            <div class="card-body p-4 position-relative">
                <div class="card-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stats-number">{{ $pendingTasks }}</div>
                <div class="stats-label">Pending Tasks</div>
                <div class="stats-change">
                    <i class="fas fa-arrow-down text-success"></i>
                    <span>-8% vs yesterday</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card stats-card h-100">
            <div class="card-body p-4 position-relative">
                <div class="card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-number">{{ $criticalIncidents }}</div>
                <div class="stats-label">Critical Incidents</div>
                <div class="stats-change">
                    <i class="fas fa-shield-alt text-success"></i>
                    <span>Under control</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics Row -->
<div class="row mb-5">
    <div class="col-lg-8 mb-4">
        <div class="card modern-card h-100">
            <div class="card-body p-4">
                <div class="chart-header">
                    <h5 class="chart-title">Patient Activity Trends</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="period" id="week" checked>
                        <label class="btn btn-outline-primary" for="week">7D</label>
                        <input type="radio" class="btn-check" name="period" id="month">
                        <label class="btn btn-outline-primary" for="month">1M</label>
                        <input type="radio" class="btn-check" name="period" id="year">
                        <label class="btn btn-outline-primary" for="year">1Y</label>
                    </div>
                </div>
                <canvas id="activityChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card modern-card h-100">
            <div class="card-body p-4">
                <h5 class="chart-title mb-4">Care Level Distribution</h5>
                <div class="text-center mb-4">
                    <canvas id="careChart" width="200" height="200"></canvas>
                </div>
                <div class="row text-center">
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
</div>

<!-- Clinical Operations Row -->
<div class="row mb-5">
    <div class="col-12">
        <div class="section-header">
            <h3 class="section-title">Clinical Operations</h3>
            <p class="section-subtitle">Monitor therapy sessions, evaluations, and patient progress</p>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-icon">
                <i class="fas fa-heart"></i>
            </div>
            <div class="metric-value">{{ $therapySessionCount }}</div>
            <div class="metric-label">Therapy Sessions</div>
            <div class="mt-2 small text-muted">24 scheduled today</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-icon bg-success">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="metric-value">{{ $evaluationCount }}</div>
            <div class="metric-label">Evaluations</div>
            <div class="mt-2 small text-muted">3 pending review</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-icon bg-warning">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="metric-value">{{ $progressReportCount }}</div>
            <div class="metric-label">Progress Reports</div>
            <div class="mt-2 small text-muted">12 due this week</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-icon bg-info">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <div class="metric-value">{{ $dischargeCount }}</div>
            <div class="metric-label">Discharges</div>
            <div class="mt-2 small text-muted">5 scheduled today</div>
        </div>
    </div>
</div>

<!-- Financial Overview and Recent Activity -->
<div class="row mb-5">
    <div class="col-lg-6 mb-4">
        <div class="card modern-card h-100">
            <div class="card-body p-4">
                <h5 class="chart-title mb-4">Financial Overview</h5>
                <canvas id="financialChart" height="120"></canvas>
                <div class="row mt-4">
                    <div class="col-6 text-center">
                        <div class="metric-value text-primary">${{ number_format($billingCount * 850) }}</div>
                        <div class="metric-label">Total Revenue</div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="metric-value text-success">{{ $paymentCount }}</div>
                        <div class="metric-label">Payments Received</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card modern-card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="chart-title">Recent Activity</h5>
                    <span class="badge bg-primary rounded-pill">{{ $notificationCount }} new</span>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon bg-primary text-white">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">New patient admitted</div>
                        <div class="small text-muted">John Smith - Room 204</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon bg-success text-white">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Therapy session completed</div>
                        <div class="small text-muted">Physical therapy - Ward A</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon bg-warning text-white">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Medication alert</div>
                        <div class="small text-muted">Low stock - Insulin</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon bg-info text-white">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Report generated</div>
                        <div class="small text-muted">Weekly patient summary</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Status Row -->
<div class="row">
    <div class="col-12">
        <div class="section-header">
            <h3 class="section-title">System Status</h3>
            <p class="section-subtitle">Monitor system health and performance metrics</p>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-icon bg-success">
                <i class="fas fa-server"></i>
            </div>
            <div class="metric-value text-success">99.9%</div>
            <div class="metric-label">System Uptime</div>
            <div class="mt-2 small text-muted">All systems operational</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-icon bg-primary">
                <i class="fas fa-database"></i>
            </div>
            <div class="metric-value">2</div>
            <div class="metric-label">System Backups</div>
            <div class="mt-2 small text-muted">Last: 3 hours ago</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-icon bg-warning">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="metric-value">78%</div>
            <div class="metric-label">Storage Used</div>
            <div class="mt-2 small text-muted">340GB available</div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card modern-card metric-card h-100">
            <div class="metric-icon bg-info">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="metric-value">3</div>
            <div class="metric-label">Security Logs</div>
            <div class="mt-2 small text-muted">All secure</div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activity Trends Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Patient Admissions',
                data: [12, 19, 15, 25, 22, 18, 24],
                borderColor: '#4A90F2',
                backgroundColor: 'rgba(74, 144, 242, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#4A90F2',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }, {
                label: 'Discharges',
                data: [8, 15, 12, 18, 16, 14, 20],
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#4CAF50',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Care Level Distribution Chart
    const careCtx = document.getElementById('careChart').getContext('2d');
    new Chart(careCtx, {
        type: 'doughnut',
        data: {
            labels: ['Intensive Care', 'Standard Care', 'Basic Care'],
            datasets: [{
                data: [45, 35, 20],
                backgroundColor: ['#4A90F2', '#4CAF50', '#17A2B8'],
                borderWidth: 0,
                cutout: '60%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Financial Overview Chart
    const financialCtx = document.getElementById('financialChart').getContext('2d');
    new Chart(financialCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue',
                data: [65000, 75000, 80000, 85000, 78000, 90000],
                backgroundColor: '#4A90F2',
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + (value / 1000) + 'K';
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endsection