<?php require_once __DIR__ . '/../_partials/gr_helpers.php'; ?>
<div class="row g-3 mt-1">
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="bi bi-kanban"></i></div>
            <div class="kpi-value"><?= $stats['total_projects'] ?? 0 ?></div>
            <div class="kpi-label">Τα Έργα Μου</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-indigo">
            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-value"><?= $stats['in_progress'] ?? 0 ?></div>
            <div class="kpi-label">Σε Εξέλιξη</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card <?= ($stats['overdue'] ?? 0) > 0 ? 'kpi-red' : 'kpi-teal' ?>">
            <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-value"><?= $stats['overdue'] ?? 0 ?></div>
            <div class="kpi-label">Εκπρόθεσμα</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-currency-euro"></i></div>
            <div class="kpi-value">€<?= number_format($stats['commission_earned'] ?? 0, 2) ?></div>
            <div class="kpi-label">Συνολικά Κερδισμένα</div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <!-- Πρόσφατα Έργα -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-kanban me-1"></i> Τα Έργα Μου</span>
                <a href="<?= APP_URL ?>/developer/projects" class="btn btn-sm btn-outline-primary">Όλα τα Έργα</a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($projects)): ?>
                <div class="text-center text-muted py-4">Δεν έχουν ανατεθεί έργα.</div>
                <?php else: ?>
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Έργο</th><th>Κατάσταση</th><th>Προτεραιότητα</th><th>Προθεσμία</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($projects as $proj): ?>
                        <?php $isOverdue = $proj['deadline'] && $proj['deadline'] < date('Y-m-d') && !in_array($proj['status'],['completed','on_hold']); ?>
                        <tr class="<?= $isOverdue?'table-danger':'' ?>">
                            <td class="fw-semibold"><?= htmlspecialchars($proj['title']) ?></td>
                            <td><span class="badge <?= match($proj['status']){'awaiting_assignment'=>'bg-secondary','in_progress'=>'bg-primary','testing'=>'bg-info text-dark','on_hold'=>'bg-warning text-dark','completed'=>'bg-success',default=>'bg-secondary'} ?>"><?= grStatus($proj['status']) ?></span></td>
                            <td><span class="badge <?= match($proj['priority']){'low'=>'bg-light text-dark','medium'=>'bg-info text-dark','high'=>'bg-warning text-dark','urgent'=>'bg-danger',default=>'bg-secondary'} ?>"><?= grPriority($proj['priority']) ?></span></td>
                            <td><?= $proj['deadline'] ? date('d M Y', strtotime($proj['deadline'])) : '—' ?></td>
                            <td><a href="<?= APP_URL ?>/developer/projects/<?= $proj['id'] ?>" class="btn btn-sm btn-outline-primary">Προβολή</a></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>

        <!-- Επερχόμενες Προθεσμίες -->
        <?php if(!empty($upcoming)): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-calendar-event me-1 text-danger"></i> Επερχόμενες Προθεσμίες (14 ημέρες)</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 table-sm">
                    <thead class="table-light"><tr><th>Έργο</th><th>Προθεσμία</th><th>Υπόλοιπες Ημέρες</th></tr></thead>
                    <tbody>
                    <?php foreach ($upcoming as $up): ?>
                        <?php $daysLeft = (int)round((strtotime($up['deadline']) - time()) / 86400); ?>
                        <tr>
                            <td><?= htmlspecialchars($up['title']) ?></td>
                            <td><?= date('d M Y', strtotime($up['deadline'])) ?></td>
                            <td><span class="badge <?= $daysLeft < 3 ? 'bg-danger' : ($daysLeft < 7 ? 'bg-warning text-dark' : 'bg-info text-dark') ?>"><?= $daysLeft ?> ημέρες</span></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif ?>
    </div>

    <!-- Πρόσφατες Προμήθειες -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-currency-euro me-1 text-success"></i> Πρόσφατες Προμήθειες</div>
            <div class="card-body p-0">
                <?php if(empty($commData)): ?>
                <div class="text-center text-muted py-4 small">Δεν υπάρχουν προμήθειες.</div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($commData as $c): ?>
                    <li class="list-group-item px-3">
                        <div class="d-flex justify-content-between">
                            <span class="small fw-semibold"><?= htmlspecialchars($c['company_name'] ?? '') ?></span>
                            <span class="fw-bold text-success">€<?= number_format($c['amount'],2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Ποσοστό: <?= $c['rate'] ?>%</span>
                            <span class="badge <?= $c['is_paid'] ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size:.65rem"><?= $c['is_paid'] ? 'Πληρωμένη' : 'Εκκρεμής' ?></span>
                        </div>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
                <div class="p-3 border-top">
                    <a href="<?= APP_URL ?>/developer/commissions" class="btn btn-sm btn-outline-success w-100">Όλες οι Προμήθειες</a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-graph-up me-1"></i> Σύνοψη Προόδου</div>
            <div class="card-body">
                <canvas id="devProgressChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stats = <?= json_encode($stats) ?>;
    new Chart(document.getElementById('devProgressChart'), {
        type: 'doughnut',
        data: {
            labels: ['Αναμονή', 'Σε Εξέλιξη', 'Ολοκληρωμένα'],
            datasets: [{
                data: [
                    Math.max(0, parseInt(stats.total_projects||0) - parseInt(stats.in_progress||0) - parseInt(stats.completed||0)),
                    parseInt(stats.in_progress||0),
                    parseInt(stats.completed||0)
                ],
                backgroundColor: ['#6c757d','#0d6efd','#198754'],
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } }, cutout: '60%' }
    });
});
</script>
