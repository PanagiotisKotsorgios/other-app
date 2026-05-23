<?php require_once __DIR__ . '/../_partials/gr_helpers.php'; ?>

<!-- ══ ROW 1: Financial KPIs ══════════════════════════════════════ -->
<div class="row g-3 mt-1">
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-value">€<?= number_format($totalRevenue,2) ?></div>
            <div class="kpi-label">Συνολικά Έσοδα</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-red">
            <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="kpi-value">€<?= number_format($totalExpenses,2) ?></div>
            <div class="kpi-label">Συνολικά Έξοδα</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-value">€<?= number_format($totalCommOwed,2) ?></div>
            <div class="kpi-label">Οφειλόμενες Προμήθειες</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card <?= $netProfit >= 0 ? 'kpi-teal' : 'kpi-red' ?>">
            <div class="kpi-icon"><i class="bi bi-bank"></i></div>
            <div class="kpi-value">€<?= number_format($netProfit,2) ?></div>
            <div class="kpi-label">Καθαρό Κέρδος</div>
        </div>
    </div>
</div>

<!-- ══ ROW 2: Operational KPIs ═══════════════════════════════════ -->
<div class="row g-3 mt-0">
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="bi bi-building"></i></div>
            <div class="kpi-value"><?= number_format($totalBiz) ?></div>
            <div class="kpi-label">Επιχειρήσεις <span class="text-warning small">(<?= $unassignedBiz ?> αναν.)</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-indigo">
            <div class="kpi-icon"><i class="bi bi-bag-check"></i></div>
            <div class="kpi-value"><?= number_format($totalDeals) ?></div>
            <div class="kpi-label">Συνολικές Συμφωνίες</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-purple">
            <div class="kpi-icon"><i class="bi bi-kanban"></i></div>
            <div class="kpi-value"><?= number_format($projStats['total'] ?? 0) ?></div>
            <div class="kpi-label">Έργα <span class="text-danger small">(<?= $projStats['overdue'] ?? 0 ?> εκπρ.)</span></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-pink">
            <div class="kpi-icon"><i class="bi bi-telephone"></i></div>
            <div class="kpi-value"><?= number_format($intThisMonth) ?></div>
            <div class="kpi-label">Κλήσεις Μήνα</div>
        </div>
    </div>
</div>

<!-- ══ ROW 3: User KPIs ══════════════════════════════════════════ -->
<div class="row g-3 mt-0">
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-indigo">
            <div class="kpi-icon"><i class="bi bi-headset"></i></div>
            <div class="kpi-value"><?= number_format($totalCallers) ?></div>
            <div class="kpi-label">Ενεργοί Τηλεφωνητές</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-teal">
            <div class="kpi-icon"><i class="bi bi-code-slash"></i></div>
            <div class="kpi-value"><?= number_format($totalDevelopers) ?></div>
            <div class="kpi-label">Ενεργοί Προγραμματιστές</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-people"></i></div>
            <div class="kpi-value"><?= number_format($totalPartners) ?></div>
            <div class="kpi-label">Ενεργοί Συνεργάτες</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="bi bi-envelope"></i></div>
            <div class="kpi-value"><?= number_format($unread) ?></div>
            <div class="kpi-label">Αδιάβαστα Μηνύματα</div>
        </div>
    </div>
</div>

<!-- ══ ROW 4: Deal + Project status bars ═════════════════════════ -->
<div class="row g-3 mt-0">
    <?php
    $dStatuses = [
        ['label'=>'Εκκρεμείς',     'key'=>'pending',     'color'=>'warning', 'icon'=>'clock'],
        ['label'=>'Εγκεκριμένες',  'key'=>'approved',    'color'=>'success', 'icon'=>'check-circle'],
        ['label'=>'Σε Εξέλιξη',    'key'=>'in_progress', 'color'=>'primary', 'icon'=>'arrow-repeat'],
        ['label'=>'Ολοκληρωμένες', 'key'=>'completed',   'color'=>'info',    'icon'=>'trophy'],
        ['label'=>'Απορριφθείσες', 'key'=>'rejected',    'color'=>'danger',  'icon'=>'x-circle'],
    ];
    foreach ($dStatuses as $ds):
    ?>
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-<?= $ds['icon'] ?> text-<?= $ds['color'] ?> fs-4 mb-1"></i>
            <div class="fs-3 fw-bold text-<?= $ds['color'] ?>"><?= $dealStats[$ds['key']] ?? 0 ?></div>
            <div class="small text-muted"><?= $ds['label'] ?></div>
        </div>
    </div>
    <?php endforeach ?>
</div>

<!-- ══ ROW 5: Main charts ════════════════════════════════════════ -->
<div class="row g-3 mt-0">
    <!-- Revenue + Deal count by month (12 months) -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart me-1 text-primary"></i>Έσοδα & Συμφωνίες (12 Μήνες)</span>
                <a href="<?= APP_URL ?>/admin/financials" class="btn btn-xs btn-outline-secondary">Πλήρη Οικονομικά</a>
            </div>
            <div class="card-body"><canvas id="revenueChart" height="110"></canvas></div>
        </div>
    </div>
    <!-- Deals by status doughnut -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pie-chart me-1 text-warning"></i>Κατάσταση Συμφωνιών</div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="dealStatusChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ══ ROW 6: Activity + Business status ════════════════════════ -->
<div class="row g-3 mt-0">
    <!-- Interactions per day (30 days) -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-activity me-1 text-success"></i>Αλληλεπιδράσεις Τελευταίων 30 Ημερών</div>
            <div class="card-body"><canvas id="activityChart" height="120"></canvas></div>
        </div>
    </div>
    <!-- Business status breakdown -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-building me-1 text-info"></i>Επιχειρήσεις ανά Κατάσταση</div>
            <div class="card-body d-flex align-items-center">
                <canvas id="bizStatusChart" height="160"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ══ ROW 7: Commissions charts ════════════════════════════════ -->
<div class="row g-3 mt-0">
    <!-- Commissions paid vs owed -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-cash-stack me-1 text-success"></i>Προμήθειες: Πληρωμένες vs Οφειλόμενες</div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="commChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <!-- Owed per caller -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-lines-fill me-1 text-orange"></i>Οφειλόμενες Προμήθειες ανά Τηλεφωνητή</div>
            <div class="card-body"><canvas id="owedChart" height="120"></canvas></div>
        </div>
    </div>
</div>

<!-- ══ ROW 8: Caller ranking + Upcoming deadlines ═══════════════ -->
<div class="row g-3 mt-0">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-trophy me-1 text-warning"></i>Κατάταξη Τηλεφωνητών</div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr><th>#</th><th>Τηλεφωνητής</th><th class="text-center">Ανατεθ.</th><th class="text-center">Κλήσεις</th><th class="text-center">Συμφ.</th><th>Έσοδα</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ranking as $i => $r): ?>
                    <tr>
                        <td><span class="badge <?= $i===0?'bg-warning text-dark':($i===1?'bg-secondary':($i===2?'bg-danger':'bg-light text-dark')) ?>"><?= $i+1 ?></span></td>
                        <td class="fw-semibold"><?= htmlspecialchars($r['name']) ?></td>
                        <td class="text-center"><?= $r['assigned'] ?></td>
                        <td class="text-center"><?= $r['interactions'] ?></td>
                        <td class="text-center"><?= $r['deals'] ?></td>
                        <td class="fw-semibold text-success">€<?= number_format($r['revenue'],0) ?></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($ranking)): ?><tr><td colspan="6" class="text-center text-muted py-3">Δεν υπάρχουν δεδομένα.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-alarm me-1 text-danger"></i>Επερχόμενες Προθεσμίες Έργων (14 Ημέρες)</div>
            <div class="card-body p-0">
                <?php if(empty($upcomingDeadlines)): ?>
                <div class="text-center py-4 text-muted small"><i class="bi bi-check-circle fs-3 d-block mb-1 text-success opacity-50"></i>Καμία επερχόμενη προθεσμία</div>
                <?php else: ?>
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light"><tr><th>Έργο</th><th>Προθεσμία</th><th>Κατάσταση</th></tr></thead>
                    <tbody>
                    <?php foreach ($upcomingDeadlines as $dl): ?>
                    <tr>
                        <td><a href="<?= APP_URL ?>/admin/projects/<?= $dl['id'] ?>" class="text-decoration-none fw-semibold"><?= htmlspecialchars($dl['name']) ?></a></td>
                        <td class="text-danger fw-semibold"><?= date('d M', strtotime($dl['deadline'])) ?></td>
                        <td><span class="badge bg-warning text-dark"><?= grStatus($dl['status']) ?></span></td>
                    </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ ROW 9: Recent deals + Top revenue deals ══════════════════ -->
<div class="row g-3 mt-0">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-1 text-secondary"></i>Πρόσφατες Συμφωνίες</span>
                <a href="<?= APP_URL ?>/admin/deals" class="btn btn-xs btn-outline-secondary">Όλες</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light"><tr><th>Εταιρία</th><th>Τηλ/τής</th><th>Ποσό</th><th>Κατάσταση</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentDeals as $d): ?>
                    <tr>
                        <td><a href="<?= APP_URL ?>/admin/deals/<?= $d['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($d['company_name']) ?></a><br><span class="text-muted" style="font-size:.74rem"><?= date('d M Y', strtotime($d['created_at'])) ?></span></td>
                        <td><?= htmlspecialchars($d['caller_name']) ?></td>
                        <td class="fw-semibold text-success">€<?= number_format($d['amount'],0) ?></td>
                        <td><span class="badge <?= dealBadgeDash($d['status']) ?>"><?= grStatus($d['status']) ?></span></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($recentDeals)): ?><tr><td colspan="4" class="text-center text-muted py-3">Καμία συμφωνία.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-star me-1 text-warning"></i>Top Συμφωνίες ανά Αξία</div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light"><tr><th>Εταιρία</th><th>Υπηρεσία</th><th>Ποσό</th><th>Κατ.</th></tr></thead>
                    <tbody>
                    <?php foreach ($topDeals as $d): ?>
                    <tr>
                        <td><a href="<?= APP_URL ?>/admin/deals/<?= $d['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($d['company_name']) ?></a></td>
                        <td class="text-muted"><?= htmlspecialchars($d['service_name'] ?? '—') ?></td>
                        <td class="fw-bold text-success">€<?= number_format($d['amount'],0) ?></td>
                        <td><span class="badge <?= dealBadgeDash($d['status']) ?>"><?= grStatus($d['status']) ?></span></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($topDeals)): ?><tr><td colspan="4" class="text-center text-muted py-3">Καμία συμφωνία.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══ ROW 10: Partner ranking + City stats ═════════════════════ -->
<div class="row g-3 mt-0 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-people me-1 text-success"></i>Κατάταξη Συνεργατών</div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light"><tr><th>Συνεργάτης</th><th>Παρ.</th><th>Έσοδα</th><th>Οφ. Προμ.</th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($partnerRanking, 0, 8) as $p): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($p['name']) ?></td>
                        <td class="text-center"><?= $p['referrals'] ?></td>
                        <td class="text-success fw-semibold">€<?= number_format($p['revenue_generated'],0) ?></td>
                        <td class="text-warning fw-semibold">€<?= number_format($p['commission_owed'],0) ?></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($partnerRanking)): ?><tr><td colspan="4" class="text-center text-muted py-3">Δεν υπάρχουν δεδομένα.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt me-1 text-primary"></i>Απόδοση ανά Πόλη</span>
                <a href="<?= APP_URL ?>/admin/financials" class="btn btn-xs btn-outline-primary">Αναλυτικά</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light"><tr><th>Πόλη</th><th>Επιχ.</th><th>Συμφ.</th><th>Έσοδα</th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($cityStats, 0, 8) as $cs): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($cs['city']) ?></td>
                        <td class="text-center"><?= $cs['total_businesses'] ?></td>
                        <td class="text-center"><?= $cs['deals'] ?></td>
                        <td class="text-success fw-semibold">€<?= number_format($cs['revenue'],0) ?></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($cityStats)): ?><tr><td colspan="4" class="text-center text-muted py-3">Δεν υπάρχουν δεδομένα.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php function dealBadgeDash(string $s): string {
    return match($s) { 'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark', default=>'bg-secondary' };
} ?>

<script>
document.addEventListener('DOMContentLoaded', function() {

    /* ── helpers ── */
    function monthLabel(ym) {
        const [y, m] = ym.split('-');
        const names = ['','Ιαν','Φεβ','Μαρ','Απρ','Μαΐ','Ιουν','Ιουλ','Αυγ','Σεπ','Οκτ','Νοε','Δεκ'];
        return names[parseInt(m)] + ' ' + y.slice(2);
    }

    /* ── 1. Revenue + Deal count by month ── */
    const revData = <?= json_encode(array_values($revenueByMonth)) ?>;
    new Chart(document.getElementById('revenueChart'), {
        data: {
            labels: revData.map(r => monthLabel(r.month)),
            datasets: [
                {
                    type: 'bar',
                    label: 'Έσοδα (€)',
                    data: revData.map(r => parseFloat(r.revenue)),
                    backgroundColor: 'rgba(13,110,253,.65)',
                    borderRadius: 5,
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Αριθμός Συμφωνιών',
                    data: revData.map(r => parseInt(r.deal_count)),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,.15)',
                    borderWidth: 2,
                    pointRadius: 4,
                    tension: .35,
                    fill: true,
                    yAxisID: 'y2',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
            scales: {
                y:  { beginAtZero: true, position: 'left',  ticks: { callback: v => '€'+v.toLocaleString('el') } },
                y2: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } }
            }
        }
    });

    /* ── 2. Deals by status doughnut ── */
    const ds = <?= json_encode($dealStats) ?>;
    new Chart(document.getElementById('dealStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Εκκρεμείς','Εγκεκριμένες','Σε Εξέλιξη','Ολοκλ/νες','Απορρ/νες'],
            datasets: [{ data: [ds.pending,ds.approved,ds.in_progress,ds.completed,ds.rejected],
                backgroundColor: ['#f59e0b','#22c55e','#3b82f6','#06b6d4','#ef4444'] }]
        },
        options: { cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } } }
    });

    /* ── 3. Interactions per day ── */
    const rawAct = <?= json_encode(array_values($activityChart)) ?>;
    // build date→{call,email,offer,demo} map
    const actMap = {};
    rawAct.forEach(r => {
        if (!actMap[r.date]) actMap[r.date] = {call:0,email:0,offer:0,demo:0};
        actMap[r.date][r.type] = parseInt(r.cnt);
    });
    const actDates = Object.keys(actMap).sort();
    new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: actDates.map(d => { const p=d.split('-'); return p[2]+'/'+p[1]; }),
            datasets: [
                { label:'Κλήσεις', data: actDates.map(d=>actMap[d].call),  borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.1)', tension:.35, fill:true, borderWidth:2, pointRadius:3 },
                { label:'Email',   data: actDates.map(d=>actMap[d].email), borderColor:'#22c55e', backgroundColor:'transparent', tension:.35, borderWidth:2, pointRadius:2 },
                { label:'Προσφορά',data: actDates.map(d=>actMap[d].offer), borderColor:'#f59e0b', backgroundColor:'transparent', tension:.35, borderWidth:2, pointRadius:2 },
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    /* ── 4. Business status horizontal bar ── */
    const bizSt = <?= json_encode(array_values($bizByStatus)) ?>;
    const bizStatusLabels = { 'new':'Νέα','contacted':'Επικοιν.','interested':'Ενδιαφέρον','not_interested':'Μη Ενδ.','deal_closed':'Deal','follow_up':'Follow-up' };
    const bizColors = { 'new':'#6b7280','contacted':'#06b6d4','interested':'#3b82f6','not_interested':'#ef4444','deal_closed':'#22c55e','follow_up':'#f59e0b' };
    new Chart(document.getElementById('bizStatusChart'), {
        type: 'bar',
        data: {
            labels: bizSt.map(r => bizStatusLabels[r.status] || r.status),
            datasets: [{ label: 'Επιχειρήσεις', data: bizSt.map(r => parseInt(r.cnt)),
                backgroundColor: bizSt.map(r => bizColors[r.status] || '#6b7280'), borderRadius: 5 }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
    });

    /* ── 5. Commissions doughnut ── */
    const cs = <?= json_encode($commStats) ?>;
    new Chart(document.getElementById('commChart'), {
        type: 'doughnut',
        data: {
            labels: ['Πληρωμένες','Οφειλόμενες'],
            datasets: [{ data: [parseFloat(cs.paid||0), parseFloat(cs.owed||0)],
                backgroundColor: ['#22c55e','#ef4444'] }]
        },
        options: { cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
    });

    /* ── 6. Owed per caller bar ── */
    const oc = <?= json_encode(array_values($owedCallers)) ?>;
    new Chart(document.getElementById('owedChart'), {
        type: 'bar',
        data: {
            labels: oc.map(r => r.name),
            datasets: [
                { label: 'Οφειλόμενο (€)', data: oc.map(r => parseFloat(r.owed||0)),
                  backgroundColor: 'rgba(239,68,68,.7)', borderRadius: 5 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '€'+v } } }
        }
    });
});
</script>
