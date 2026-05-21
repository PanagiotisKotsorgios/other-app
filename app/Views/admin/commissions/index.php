<?php use App\Core\CSRF; ?>
<!-- Κάρτες Σύνοψης -->
<div class="row g-3 mb-3 mt-1">
    <div class="col-md-4">
        <div class="kpi-card kpi-blue"><div class="kpi-icon"><i class="bi bi-currency-euro"></i></div><div class="kpi-value">€<?= number_format($stats['total_commissions']??0,2) ?></div><div class="kpi-label">Συνολικές Προμήθειες</div></div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card kpi-green"><div class="kpi-icon"><i class="bi bi-check-circle"></i></div><div class="kpi-value">€<?= number_format($stats['paid']??0,2) ?></div><div class="kpi-label">Πληρωμένες</div></div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card kpi-red"><div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div><div class="kpi-value">€<?= number_format($stats['owed']??0,2) ?></div><div class="kpi-label">Οφειλόμενες</div></div>
    </div>
</div>

<!-- Φίλτρα -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="is_paid" class="form-select form-select-sm">
                    <option value="">Όλες</option>
                    <option value="0" <?= ($filters['is_paid']==='0')?'selected':'' ?>>Μη Πληρωμένες</option>
                    <option value="1" <?= ($filters['is_paid']==='1')?'selected':'' ?>>Πληρωμένες</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="caller_id" class="form-select form-select-sm">
                    <option value="">Όλοι οι Τηλεφωνητές</option>
                    <?php foreach($callers as $c): ?><option value="<?= $c['id'] ?>" <?= $filters['caller_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option><?php endforeach ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Φίλτρο</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Τηλεφωνητής</th><th>Επιχείρηση</th><th>Υπηρεσία</th><th>Ποσό Συμφωνίας</th><th>Προμήθεια</th><th>Ποσοστό</th><th>Κατάσταση</th><th>Πληρώθηκε</th><th>Ενέργειες</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $c): ?>
            <tr>
                <td class="small text-muted">#<?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['caller_name']) ?></td>
                <td><?= htmlspecialchars($c['company_name']) ?></td>
                <td><?= htmlspecialchars($c['service_name']??'—') ?></td>
                <td>€<?= number_format($c['deal_amount'],2) ?></td>
                <td class="fw-bold text-success">€<?= number_format($c['amount'],2) ?></td>
                <td><?= $c['rate'] ?>%</td>
                <td><span class="badge <?= $c['is_paid']?'bg-success':'bg-warning text-dark' ?>"><?= $c['is_paid']?'Πληρωμένη':'Εκκρεμής' ?></span></td>
                <td class="small text-muted"><?= $c['paid_at'] ? date('d M Y', strtotime($c['paid_at'])) : '—' ?></td>
                <td>
                    <?php if(!$c['is_paid']): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/commissions/<?= $c['id'] ?>/paid" class="d-inline">
                        <?= CSRF::field() ?>
                        <button class="btn btn-xs btn-success">Σήμανση Πληρωμένης</button>
                    </form>
                    <?php else: ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/commissions/<?= $c['id'] ?>/unpaid" class="d-inline">
                        <?= CSRF::field() ?>
                        <button class="btn btn-xs btn-outline-warning">Σήμανση Εκκρεμούς</button>
                    </form>
                    <?php endif ?>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="10" class="text-center py-4 text-muted">Δεν βρέθηκαν προμήθειες.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/../../_partials/pagination.php' ?></div><?php endif ?>
</div>
