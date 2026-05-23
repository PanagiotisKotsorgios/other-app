<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>

<div class="d-flex justify-content-end gap-2 mt-2 mb-1">
    <a href="<?= APP_URL ?>/admin/financials/export/excel" class="btn btn-sm btn-outline-success">
        <i class="bi bi-file-earmark-excel me-1"></i>Εξαγωγή Excel
    </a>
    <a href="<?= APP_URL ?>/admin/financials/export/pdf" class="btn btn-sm btn-outline-danger">
        <i class="bi bi-file-earmark-pdf me-1"></i>Εξαγωγή PDF
    </a>
</div>

<div class="row g-3 mt-1">
    <!-- Κάρτες Σύνοψης -->
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-value">€<?= number_format($totalRevenue, 2) ?></div>
            <div class="kpi-label">Συνολικά Έσοδα</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-red">
            <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="kpi-value">€<?= number_format($totalExpenses, 2) ?></div>
            <div class="kpi-label">Συνολικά Έξοδα</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card <?= $netProfit >= 0 ? 'kpi-teal' : 'kpi-orange' ?>">
            <div class="kpi-icon"><i class="bi bi-bank"></i></div>
            <div class="kpi-value">€<?= number_format($netProfit, 2) ?></div>
            <div class="kpi-label">Καθαρό Κέρδος</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-value">€<?= number_format($totalComm, 2) ?></div>
            <div class="kpi-label">Οφειλόμενες Προμήθειες</div>
        </div>
    </div>
</div>

<!-- Ανάλυση Προμηθειών ανά Ρόλο -->
<div class="row g-3 mt-0">
    <?php
    $callerOwed = (float)($commStats['owed_callers'] ?? 0);
    $devOwed    = (float)($commStats['owed_developers'] ?? 0);
    $partOwed   = (float)($commStats['owed_partners'] ?? 0);
    ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-4 border-primary">
            <div class="fs-4 fw-bold text-primary">€<?= number_format($callerOwed, 2) ?></div>
            <div class="small text-muted">Οφειλόμενα σε Τηλεφωνητές</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-4 border-success">
            <div class="fs-4 fw-bold text-success">€<?= number_format($devOwed, 2) ?></div>
            <div class="small text-muted">Οφειλόμενα σε Προγραμματιστές</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-4 border-warning">
            <div class="fs-4 fw-bold text-warning">€<?= number_format($partOwed, 2) ?></div>
            <div class="small text-muted">Οφειλόμενα σε Συνεργάτες</div>
        </div>
    </div>
</div>

<!-- Γραφήματα -->
<div class="row g-3 mt-0">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Έσοδα ανά Μήνα (Τελευταίοι 12 Μήνες)</div>
            <div class="card-body"><canvas id="revenueMonthChart" height="100"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Έξοδα ανά Κατηγορία</div>
            <div class="card-body"><canvas id="expCatChart" height="250"></canvas></div>
        </div>
    </div>
</div>

<!-- Κορυφαίες 10 Συμφωνίες -->
<div class="row g-3 mt-0">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-trophy me-1 text-warning"></i> Κορυφαίες 10 Συμφωνίες</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Επιχείρηση</th><th>Τηλεφωνητής</th><th>Κατάσταση</th><th>Ποσό Συμφωνίας</th><th>Προμήθειες</th><th>Έξοδα</th><th>Καθαρό</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topDeals as $i => $deal): ?>
                            <?php
                            $net = $deal['amount'] - $deal['total_commissions'] - $deal['total_expenses'];
                            ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><a href="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>"><?= htmlspecialchars($deal['company_name']) ?></a></td>
                                <td><?= htmlspecialchars($deal['caller_name']) ?></td>
                                <td><span class="badge <?= match($deal['status']){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>"><?= grStatus($deal['status']) ?></span></td>
                                <td class="fw-semibold text-success">€<?= number_format($deal['amount'],2) ?></td>
                                <td class="text-warning">€<?= number_format($deal['total_commissions'],2) ?></td>
                                <td class="text-danger">€<?= number_format($deal['total_expenses'],2) ?></td>
                                <td class="fw-bold <?= $net >= 0 ? 'text-success' : 'text-danger' ?>">€<?= number_format($net,2) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if(empty($topDeals)): ?><tr><td colspan="8" class="text-center text-muted py-3">Δεν υπάρχουν εγκεκριμένες συμφωνίες.</td></tr><?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Οικονομικά ανά Έργο -->
<div class="row g-3 mt-0">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Οικονομική Ανάλυση ανά Έργο</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Επιχείρηση</th><th>Έργο</th><th>Ποσό Συμφωνίας</th><th>Προμήθειες</th><th>Έξοδα</th><th>Καθαρό</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($perProject as $row): ?>
                            <?php $net = $row['deal_amount'] - $row['commissions'] - $row['expenses']; ?>
                            <tr>
                                <td><?= htmlspecialchars($row['company_name']) ?></td>
                                <td><?= htmlspecialchars($row['project_title'] ?? '—') ?></td>
                                <td>€<?= number_format($row['deal_amount'],2) ?></td>
                                <td class="text-warning">€<?= number_format($row['commissions'],2) ?></td>
                                <td class="text-danger">€<?= number_format($row['expenses'],2) ?></td>
                                <td class="fw-semibold <?= $net >= 0 ? 'text-success' : 'text-danger' ?>">€<?= number_format($net,2) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if(empty($perProject)): ?><tr><td colspan="6" class="text-center text-muted py-3">Δεν υπάρχουν δεδομένα.</td></tr><?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Κέρδη ανά Ρόλο -->
<div class="row g-3 mt-0">
    <!-- Τηλεφωνητές -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-people me-1 text-primary"></i> Κέρδη Τηλεφωνητών</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 table-sm">
                    <thead class="table-light"><tr><th>Τηλεφωνητής</th><th>Οφειλόμενο</th></tr></thead>
                    <tbody>
                    <?php foreach ($callerEarnings as $ce): ?>
                        <tr>
                            <td><?= htmlspecialchars($ce['name']) ?></td>
                            <td class="text-warning fw-semibold">€<?= number_format($ce['owed'],2) ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php if(empty($callerEarnings)): ?><tr><td colspan="2" class="text-center text-muted py-2">Κανένας</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Προγραμματιστές -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-code-slash me-1 text-success"></i> Κέρδη Προγραμματιστών</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 table-sm">
                    <thead class="table-light"><tr><th>Προγραμματιστής</th><th>Κερδισμένα</th><th>Οφειλόμενο</th></tr></thead>
                    <tbody>
                    <?php foreach ($developerEarnings as $de): ?>
                        <tr>
                            <td><?= htmlspecialchars($de['name']) ?></td>
                            <td class="text-success">€<?= number_format($de['total_earned'],2) ?></td>
                            <td class="text-warning fw-semibold">€<?= number_format($de['owed'],2) ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php if(empty($developerEarnings)): ?><tr><td colspan="3" class="text-center text-muted py-2">Κανένας</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Συνεργάτες -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-handshake me-1 text-warning"></i> Κέρδη Συνεργατών</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 table-sm">
                    <thead class="table-light"><tr><th>Συνεργάτης</th><th>Κερδισμένα</th><th>Οφειλόμενο</th></tr></thead>
                    <tbody>
                    <?php foreach ($partnerEarnings as $pe): ?>
                        <tr>
                            <td><?= htmlspecialchars($pe['name']) ?></td>
                            <td class="text-success">€<?= number_format($pe['total_earned'],2) ?></td>
                            <td class="text-warning fw-semibold">€<?= number_format($pe['owed'],2) ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php if(empty($partnerEarnings)): ?><tr><td colspan="3" class="text-center text-muted py-2">Κανένας</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Πρόσφατα Έξοδα -->
<div class="row g-3 mt-0">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-1"></i> Πρόσφατα Έξοδα</span>
                <a href="<?= APP_URL ?>/admin/financials/expenses" class="btn btn-sm btn-outline-secondary">Διαχείριση Όλων</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Περιγραφή</th><th>Κατηγορία</th><th>Ποσό</th><th>Ημερομηνία</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentExpenses as $exp): ?>
                        <tr>
                            <td><?= htmlspecialchars($exp['description']) ?></td>
                            <td><span class="badge bg-secondary"><?= grExpCat($exp['category']) ?></span></td>
                            <td class="text-danger fw-semibold">€<?= number_format($exp['amount'],2) ?></td>
                            <td><?= $exp['expense_date'] ? date('d M Y', strtotime($exp['expense_date'])) : '—' ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php if(empty($recentExpenses)): ?><tr><td colspan="4" class="text-center text-muted py-3">Δεν έχουν καταγραφεί έξοδα.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Έσοδα ανά μήνα
    const revData = <?= json_encode($revenueChart) ?>;
    new Chart(document.getElementById('revenueMonthChart'), {
        type: 'bar',
        data: {
            labels: revData.map(r => r.month),
            datasets: [{
                label: 'Έσοδα (€)',
                data: revData.map(r => parseFloat(r.revenue || 0)),
                backgroundColor: 'rgba(25,135,84,.75)',
                borderRadius: 5,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // Έξοδα ανά κατηγορία
    const expData = <?= json_encode($expByCategory) ?>;
    if (expData.length > 0) {
        new Chart(document.getElementById('expCatChart'), {
            type: 'doughnut',
            data: {
                labels: expData.map(e => e.category.charAt(0).toUpperCase() + e.category.slice(1)),
                datasets: [{
                    data: expData.map(e => parseFloat(e.total)),
                    backgroundColor: ['#0d6efd','#198754','#dc3545','#fd7e14','#6610f2','#0dcaf0','#ffc107','#6c757d'],
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } }, cutout: '55%' }
        });
    }
});
</script>
