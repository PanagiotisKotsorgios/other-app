<!-- E:\call_center\app\Views\admin\dashboard.php -->
<div class="row g-3 mt-1">
    <!-- Financial Summary Cards -->
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-value">€<?= number_format($totalRevenue, 2) ?></div>
            <div class="kpi-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-red">
            <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="kpi-value">€<?= number_format($totalExpenses, 2) ?></div>
            <div class="kpi-label">Total Expenses</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-value">€<?= number_format($totalCommOwed, 2) ?></div>
            <div class="kpi-label">Commissions Owed</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card <?= $netProfit >= 0 ? 'kpi-teal' : 'kpi-red' ?>">
            <div class="kpi-icon"><i class="bi bi-bank"></i></div>
            <div class="kpi-value">€<?= number_format($netProfit, 2) ?></div>
            <div class="kpi-label">Net Profit</div>
        </div>
    </div>

    <!-- Operational KPIs -->
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="bi bi-building"></i></div>
            <div class="kpi-value"><?= number_format($totalBiz) ?></div>
            <div class="kpi-label">Total Businesses</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-indigo">
            <div class="kpi-icon"><i class="bi bi-people"></i></div>
            <div class="kpi-value"><?= number_format($totalCallers) ?></div>
            <div class="kpi-label">Active Callers</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-purple">
            <div class="kpi-icon"><i class="bi bi-kanban"></i></div>
            <div class="kpi-value"><?= number_format($projStats['total'] ?? 0) ?></div>
            <div class="kpi-label">Projects</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-pink">
            <div class="kpi-icon"><i class="bi bi-telephone"></i></div>
            <div class="kpi-value"><?= number_format($intStats['total_calls'] ?? 0) ?></div>
            <div class="kpi-label">Total Calls</div>
        </div>
    </div>
</div>

<!-- Deal Stats Row -->
<div class="row g-3 mt-0">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-warning"><?= $dealStats['pending'] ?? 0 ?></div>
            <div class="small text-muted">Deals Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-success"><?= $dealStats['approved'] ?? 0 ?></div>
            <div class="small text-muted">Deals Approved</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary"><?= $dealStats['in_progress'] ?? 0 ?></div>
            <div class="small text-muted">In Progress</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-info"><?= $dealStats['completed'] ?? 0 ?></div>
            <div class="small text-muted">Completed</div>
        </div>
    </div>
</div>

<!-- Project Stats Row -->
<div class="row g-3 mt-0">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-secondary"><?= $projStats['awaiting'] ?? 0 ?></div>
            <div class="small text-muted">Awaiting Assignment</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-primary"><?= $projStats['in_progress'] ?? 0 ?></div>
            <div class="small text-muted">Projects In Progress</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-danger"><?= $projStats['overdue'] ?? 0 ?></div>
            <div class="small text-muted">Overdue Projects</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-3 fw-bold text-success"><?= $projStats['completed'] ?? 0 ?></div>
            <div class="small text-muted">Projects Completed</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mt-0">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Revenue (Last 6 Months)</div>
            <div class="card-body"><canvas id="revenueChart" height="100"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Commissions</div>
            <div class="card-body"><canvas id="commChart" height="200"></canvas></div>
        </div>
    </div>
</div>

<!-- Ranking + City Stats -->
<div class="row g-3 mt-0">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-trophy me-1 text-warning"></i> Caller Rankings</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Caller</th><th>Assigned</th><th>Interactions</th><th>Deals</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ranking as $i => $r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= $r['assigned'] ?></td>
                            <td><?= $r['interactions'] ?></td>
                            <td><?= $r['deals'] ?></td>
                            <td class="fw-semibold text-success">€<?= number_format($r['revenue'],2) ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php if(empty($ranking)): ?><tr><td colspan="6" class="text-center text-muted py-3">No data yet.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt me-1 text-primary"></i> Performance by City</span>
                <a href="<?= APP_URL ?>/admin/financials" class="btn btn-sm btn-outline-primary">Full Financials</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>City</th><th>Businesses</th><th>Deals</th><th>Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($cityStats as $cs): ?>
                        <tr>
                            <td><?= htmlspecialchars($cs['city']) ?></td>
                            <td><?= $cs['total_businesses'] ?></td>
                            <td><?= $cs['deals'] ?></td>
                            <td>€<?= number_format($cs['revenue'],2) ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php if(empty($cityStats)): ?><tr><td colspan="4" class="text-center text-muted py-3">No data.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue chart
    const revData = <?= json_encode($revenueChart) ?>;
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: revData.map(r => r.month),
            datasets: [{
                label: 'Revenue (€)',
                data: revData.map(r => parseFloat(r.revenue)),
                backgroundColor: 'rgba(13,110,253,.7)',
                borderRadius: 6,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // Commission donut
    const cs = <?= json_encode($commStats) ?>;
    new Chart(document.getElementById('commChart'), {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Owed'],
            datasets: [{ data: [parseFloat(cs.paid||0), parseFloat(cs.owed||0)], backgroundColor: ['#198754','#dc3545'] }]
        },
        options: { plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
    });
});
</script>
