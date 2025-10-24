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
        border-radius: 12px;
        box-shadow: 0 6px 24px rgba(74, 144, 242, 0.06);
        transition: all 0.25s ease;
    }

    .stats-card { color:#fff; border-radius:12px; padding:1rem; height:100%; }
    .stats-number { font-size:2rem; font-weight:700; }
    .stats-label { font-size:0.95rem; opacity:.9; }

    .chart-wrapper { height: 260px; }

    .metric-card { background: var(--light-gray); border-radius: 12px; padding: 1rem; text-align:center; }
    .metric-value { font-size:1.6rem; font-weight:700; color:var(--dark-blue); }
    .metric-label { color:var(--medium-gray); font-weight:500; }

    .section-title { font-size:1.25rem; font-weight:700; color:var(--dark-blue); }
    .section-subtitle { color:var(--medium-gray); font-size:0.95rem; }
</style>

<div class="container-fluid">
    <!-- Alerts -->
    <div class="row mb-4">
        <div class="col-12">
            @if($noRoleCount > 0)
                <div class="alert alert-warning d-flex justify-content-between align-items-center">
                    <div><strong>{{ $noRoleCount }}</strong> user{{ $noRoleCount > 1 ? 's' : '' }} found without assigned roles.</div>
                    <div><button class="btn btn-sm btn-outline-dark" onclick="location.href='{{ route('users.index') }}'">Manage Users</button></div>
                </div>
            @else
                <div class="alert alert-success">All users have roles assigned.</div>
            @endif
        </div>
    </div>

    <!-- KPI cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="modern-card stats-card" style="background:linear-gradient(135deg,var(--primary-blue),var(--secondary-blue)); padding:1.25rem;">
                <div class="stats-number">{{ $patientCount }}</div>
                <div class="stats-label">Total Patients</div>
                <div class="small mt-2">Recent: {{ $recentPatients->count() }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="modern-card stats-card" style="background:linear-gradient(135deg,#56CCF2,#2F80ED); padding:1.25rem;">
                <div class="stats-number">{{ $staffCount }}</div>
                <div class="stats-label">Active Staff</div>
                <div class="small mt-2">Recent hires: {{ $recentStaff->count() }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="modern-card stats-card" style="background:linear-gradient(135deg,#4CAF50,#2E7D32); padding:1.25rem;">
                <div class="stats-number">{{ $pendingTasks }}</div>
                <div class="stats-label">Pending Tasks</div>
                <div class="small mt-2">Today: {{ $todayAppointments }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="modern-card stats-card" style="background:linear-gradient(135deg,#FF7043,#F44336); padding:1.25rem;">
                <div class="stats-number">{{ $criticalIncidents }}</div>
                <div class="stats-label">Critical Incidents</div>
                <div class="small mt-2">Recent: {{ $recentIncidents->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Trends & Chart -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="modern-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Patient Activity Trends</h5>
                        <small class="text-muted">Invoiced vs Payments (last 6 months)</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="financialChartLarge"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="modern-card p-3">
                <h6 class="mb-3">Care Level Distribution</h6>
                <div class="chart-wrapper d-flex align-items-center justify-content-center">
                    <canvas id="careChartSmall" style="max-width:220px;"></canvas>
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

    <!-- Clinical & Financial -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-card metric-card">
                <div class="metric-value">{{ $therapySessionCount }}</div>
                <div class="metric-label">Therapy Sessions</div>
                <div class="small text-muted mt-1">Scheduled</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-card metric-card">
                <div class="metric-value">{{ $progressReportCount }}</div>
                <div class="metric-label">Progress Reports</div>
                <div class="small text-muted mt-1">Pending</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-card metric-card">
                <div class="metric-value">{{ $dischargeCount }}</div>
                <div class="metric-label">Discharges</div>
                <div class="small text-muted mt-1">Today</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-card metric-card">
                <div class="metric-value">${{ number_format($totalRevenue, 2) }}</div>
                <div class="metric-label">Total Revenue</div>
                <div class="small text-muted mt-1">{{ $paymentCount }} payments received</div>
            </div>
        </div>
    </div>

    <!-- Recent activity rows -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="modern-card p-3">
                <h6>Recent Payments</h6>
                <div class="list-group">
                    @forelse($recentPayments as $p)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div><strong>${{ number_format($p->amount, 2) }}</strong> — {{ ucfirst($p->method) }}</div>
                            <div class="small text-muted">Invoice: {{ $p->invoice->invoice_number ?? '—' }} • Paid at: {{ optional($p->paid_at)->format('Y-m-d H:i') }}</div>
                            <div class="small text-muted">{{ optional($p->receiver)->name ?? '—' }}</div>
                            
                            <div class="small text-muted">{{ optional($p->receiver)->name ?? '—' }}</div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted">No recent payments</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="modern-card p-3">
                <h6>Recent Notifications</h6>
                <div class="list-group">
                    @forelse($recentNotifications as $n)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-bold">{{ $n->title ?? Str::limit($n->data['message'] ?? ($n->data['body'] ?? ''), 80) }}</div>
                                    <div class="small text-muted">{{ optional($n->created_at)->diffForHumans() }}</div>
                                </div>
                                <div class="small text-muted">{{ optional($n->author)->name ?? '' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted">No recent notifications</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Audit logs --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-card p-3">
                <h6>Recent Audit Logs</h6>
                <ul class="list-group list-group-flush">
                    @forelse($auditLogs as $log)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted">{{ optional($log->timestamp)->format('Y-m-d H:i') }}</div>
                                <div>{{ Str::limit($log->action ?? $log->message ?? '—', 120) }}</div>
                            </div>
                            <div class="small text-muted">{{ optional($log->user)->name ?? 'system' }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No audit logs</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <!-- Chart.js (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Safely read chart payload (use empty arrays when missing)
        const labels   = @json($chart['labels'] ?? []);
        const invoices = @json($chart['invoices'] ?? []);
        const payments = @json($chart['payments'] ?? []);

        // Helper to format currency in tooltip
        function currencyTooltip(value) {
            try {
                return '$' + Number(value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } catch (e) {
                return '$' + Number(value).toFixed(2);
            }
        }

        // Large financial chart
        const financialCanvas = document.getElementById('financialChartLarge');
        if (financialCanvas && typeof Chart !== 'undefined') {
            const ctx = financialCanvas.getContext('2d');
            // Destroy existing chart instance if re-rendered by SPA or Turbo-like navigation
            if (financialCanvas._chartInstance) {
                try { financialCanvas._chartInstance.destroy(); } catch (e) {}
            }
            financialCanvas._chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Invoiced',
                            data: invoices,
                            backgroundColor: 'rgba(74,144,242,0.25)',
                            borderColor: '#4A90F2',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Payments',
                            data: payments,
                            type: 'line',
                            backgroundColor: 'rgba(16,185,129,0.15)',
                            borderColor: '#10b981',
                            borderWidth: 2,
                            tension: 0.35,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => '$' + Number(v).toLocaleString() } }
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const v = context.parsed.y ?? context.parsed;
                                    return `${context.dataset.label}: ${currencyTooltip(v)}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Small care chart (static demo values)
        const careCanvas = document.getElementById('careChartSmall');
        if (careCanvas && typeof Chart !== 'undefined') {
            const ctx2 = careCanvas.getContext('2d');
            if (careCanvas._chartInstance) {
                try { careCanvas._chartInstance.destroy(); } catch (e) {}
            }
            careCanvas._chartInstance = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Intensive','Standard','Basic'],
                    datasets: [{ data: [45,35,20], backgroundColor: ['#4A90F2','#4CAF50','#17A2B8'] }]
                },
                options: { maintainAspectRatio: false, responsive: true, plugins: { legend: { display: false } } }
            });
        }
    });
    </script>
@endpush
@endsection

