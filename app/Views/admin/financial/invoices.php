<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>
<!-- Κάρτες Σύνοψης -->
<div class="row g-3 mb-3 mt-1">
    <div class="col-md-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="kpi-value">€<?= number_format($stats['total_invoiced'] ?? 0, 2) ?></div>
            <div class="kpi-label">Συνολικά Τιμολογημένα</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
            <div class="kpi-value">€<?= number_format($stats['collected'] ?? 0, 2) ?></div>
            <div class="kpi-label">Εισπραχθέντα</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card kpi-red">
            <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="kpi-value">€<?= number_format($stats['outstanding'] ?? 0, 2) ?></div>
            <div class="kpi-label">Εκκρεμή</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="kpi-value"><?= $stats['invoice_count'] ?? 0 ?></div>
            <div class="kpi-label">Σύνολο Τιμολογίων</div>
        </div>
    </div>
</div>

<!-- Φίλτρα -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση εταιρίας..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Όλες οι Καταστάσεις</option>
                    <?php foreach (['draft','issued','sent','paid'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= grStatus($s) ?></option>
                    <?php endforeach ?>
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
                <tr>
                    <th>#</th><th>Αρ. Τιμολογίου</th><th>Επιχείρηση</th><th>Ποσό</th>
                    <th>ΦΠΑ</th><th>Σύνολο</th><th>Κατάσταση</th><th>Εκδόθηκε</th><th>Λήξη</th><th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $inv): ?>
            <tr>
                <td class="text-muted small">#<?= $inv['id'] ?></td>
                <td><?= htmlspecialchars($inv['invoice_no'] ?? '—') ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($inv['company_name'] ?? '—') ?></td>
                <td>€<?= number_format($inv['amount'], 2) ?></td>
                <td><?= $inv['vat_rate'] ?>% (€<?= number_format($inv['vat_amount'], 2) ?>)</td>
                <td class="fw-bold">€<?= number_format($inv['total_amount'], 2) ?></td>
                <td>
                    <span class="badge <?= match($inv['status']) {
                        'draft'  => 'bg-secondary',
                        'issued' => 'bg-info text-dark',
                        'sent'   => 'bg-primary',
                        'paid'   => 'bg-success',
                        default  => 'bg-secondary'
                    } ?>"><?= grStatus($inv['status']) ?></span>
                </td>
                <td class="small text-muted"><?= $inv['issued_at'] ? date('d M Y', strtotime($inv['issued_at'])) : '—' ?></td>
                <td class="small <?= ($inv['due_at'] && $inv['status'] !== 'paid' && strtotime($inv['due_at']) < time()) ? 'text-danger fw-semibold' : 'text-muted' ?>">
                    <?= $inv['due_at'] ? date('d M Y', strtotime($inv['due_at'])) : '—' ?>
                </td>
                <td class="d-flex gap-1">
                    <a href="<?= APP_URL ?>/admin/deals/<?= $inv['deal_id'] ?>" class="btn btn-xs btn-outline-primary" title="Προβολή Συμφωνίας"><i class="bi bi-eye"></i></a>
                    <?php if ($inv['filename']): ?>
                    <a href="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/download" class="btn btn-xs btn-outline-secondary" title="Λήψη"><i class="bi bi-download"></i></a>
                    <?php endif ?>
                    <?php if ($inv['status'] !== 'paid'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/mark-paid" class="d-inline">
                        <?= CSRF::field() ?>
                        <button class="btn btn-xs btn-outline-success" title="Σήμανση Πληρωμένου"><i class="bi bi-check2"></i></button>
                    </form>
                    <?php endif ?>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($data)): ?>
            <tr><td colspan="10" class="text-center py-4 text-muted">Δεν βρέθηκαν τιμολόγια.</td></tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if (($last_page ?? 1) > 1): ?>
    <div class="card-footer bg-white"><?php include __DIR__ . '/../../_partials/pagination.php' ?></div>
    <?php endif ?>
</div>
