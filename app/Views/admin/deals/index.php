<?php require_once __DIR__ . '/../../_partials/gr_helpers.php'; use App\Core\CSRF; ?>
<div class="d-flex justify-content-end align-items-center mt-2 mb-3">
    <button type="button" class="btn btn-danger btn-sm d-none" id="bulkDeleteBtn">
        <i class="bi bi-trash me-1"></i>Διαγραφή Επιλεγμένων (<span id="bulkCount">0</span>)
    </button>
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση εταιρίας ή τηλεφωνητή..." value="<?= htmlspecialchars($filters['search']) ?>"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Όλες οι Καταστάσεις</option>
                    <?php foreach (['pending','approved','rejected','in_progress','completed'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= grStatus($s) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="caller_id" class="form-select form-select-sm">
                    <option value="">Όλοι οι Τηλεφωνητές</option>
                    <?php foreach ($callers as $c): ?><option value="<?= $c['id'] ?>" <?= $filters['caller_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option><?php endforeach ?>
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
                <th style="width:36px"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                <th>#</th><th>Επιχείρηση</th><th>Τηλεφωνητής</th><th>Υπηρεσία</th><th>Ποσό</th><th>Κατάσταση</th><th>Ημερομηνία</th><th>Ενέργειες</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $d): ?>
            <tr>
                <td><input type="checkbox" class="form-check-input row-check" value="<?= $d['id'] ?>"></td>
                <td class="text-muted small">#<?= $d['id'] ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($d['company_name']) ?></td>
                <td><?= htmlspecialchars($d['caller_name']) ?></td>
                <td><?= htmlspecialchars($d['service_name']??'—') ?></td>
                <td class="fw-semibold">€<?= number_format($d['amount'],2) ?></td>
                <td><span class="badge <?= dealBadge($d['status']) ?>"><?= grStatus($d['status']) ?></span></td>
                <td class="small text-muted"><?= date('d M Y', strtotime($d['created_at'])) ?></td>
                <td><a href="<?= APP_URL ?>/admin/deals/<?= $d['id'] ?>" class="btn btn-xs btn-outline-primary">Προβολή</a></td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="9" class="text-center py-4 text-muted">Δεν βρέθηκαν συμφωνίες.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/../../_partials/pagination.php' ?></div><?php endif ?>
</div>

<?php function dealBadge(string $s): string {
    return match($s) { 'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark', default=>'bg-secondary' };
} ?>
<form id="bulkDeleteForm" method="POST" action="<?= APP_URL ?>/admin/deals/bulk-delete" class="d-none">
    <?= CSRF::field() ?>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initBulkDelete('.row-check', 'checkAll', 'bulkCount', 'bulkDeleteBtn', 'bulkDeleteForm',
        'Διαγραφή {n} συμφωνιών; Θα διαγραφούν επίσης οι προμήθειές τους. Η ενέργεια είναι μόνιμη.');
});
</script>
