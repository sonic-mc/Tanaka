<div class="col-lg-4 mb-4">
    <div class="card modern-card chart-card">
        <h5 class="chart-title">Patient Progress Distribution</h5>
        <div class="chart-wrapper d-flex justify-content-center">
            <canvas id="progressChart" width="200" height="200"></canvas>
        </div>
        <div class="row text-center mt-3">
            <div class="col-4">
                <div class="metric-value text-success">{{ $progressDistribution['percentages']['improved'] ?? 0 }}%</div>
                <div class="metric-label">Improved</div>
            </div>
            <div class="col-4">
                <div class="metric-value text-warning">{{ $progressDistribution['percentages']['stable'] ?? 0 }}%</div>
                <div class="metric-label">Stable</div>
            </div>
            <div class="col-4">
                <div class="metric-value text-danger">{{ $progressDistribution['percentages']['declined'] ?? 0 }}%</div>
                <div class="metric-label">Declined</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    function ensureChartJs(cb) {
        if (window.Chart) return cb();
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
        s.onload = cb;
        document.head.appendChild(s);
    }

    ensureChartJs(function() {
        const ctx = document.getElementById('progressChart').getContext('2d');
        const data = @json($progressDistribution['chart']['data'] ?? [0,0,0]);
        const labels = @json($progressDistribution['chart']['labels'] ?? ['Improved','Stable','Declined']);
        const colors = @json($progressDistribution['chart']['colors'] ?? ['#22c55e', '#f59e0b', '#ef4444']);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    tooltip: { enabled: true }
                },
                cutout: '60%',
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
})();
</script>
@endpush
