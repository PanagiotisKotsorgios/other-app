<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>

<!-- Κάρτες Σύνοψης -->
<div class="row g-3 mt-1 mb-3">
    <div class="col-md-4">
        <div class="kpi-card kpi-red">
            <div class="kpi-icon"><i class="bi bi-wallet2"></i></div>
            <div class="kpi-value">€<?= number_format($stats['total_expenses'] ?? 0, 2) ?></div>
            <div class="kpi-label">Συνολικά Έξοδα</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="kpi-value"><?= $stats['expense_count'] ?? 0 ?></div>
            <div class="kpi-label">Εγγραφές Εξόδων</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <div class="fw-semibold mb-2 small">Ανά Κατηγορία</div>
            <?php foreach ($byCat as $cat): ?>
            <div class="d-flex justify-content-between small mb-1">
                <span><?= grExpCat($cat['category']) ?></span>
                <span class="fw-semibold text-danger">€<?= number_format($cat['total'],2) ?></span>
            </div>
            <?php endforeach ?>
        </div>
    </div>
</div>

<!-- Εργαλειοθήκη -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="GET" class="d-flex gap-2">
        <select name="category" class="form-select form-select-sm">
            <option value="">Όλες οι Κατηγορίες</option>
            <?php foreach (['hosting','software','hardware','subcontractor','marketing','salary','tax','other'] as $cat): ?>
            <option value="<?= $cat ?>" <?= ($filters['category']??'')===$cat?'selected':'' ?>><?= grExpCat($cat) ?></option>
            <?php endforeach ?>
        </select>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση..." value="<?= htmlspecialchars($filters['search']??'') ?>">
        <button class="btn btn-sm btn-outline-secondary">Φίλτρο</button>
    </form>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="bi bi-plus-lg me-1"></i>Προσθήκη Εξόδου
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Περιγραφή</th><th>Κατηγορία</th><th>Ποσό</th><th>Έργο</th><th>Ημερομηνία</th><th>Καταχωρήθηκε</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($data as $exp): ?>
                    <tr>
                        <td><?= htmlspecialchars($exp['description']) ?></td>
                        <td><span class="badge bg-secondary"><?= grExpCat($exp['category']) ?></span></td>
                        <td class="fw-bold text-danger">€<?= number_format($exp['amount'],2) ?></td>
                        <td class="small text-muted"><?= htmlspecialchars($exp['project_title'] ?? ($exp['company_name'] ?? '—')) ?></td>
                        <td class="text-muted small"><?= $exp['expense_date'] ? date('d M Y', strtotime($exp['expense_date'])) : '—' ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($exp['created_by_name'] ?? '—') ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editExpenseModal"
                                data-id="<?= $exp['id'] ?>"
                                data-description="<?= htmlspecialchars($exp['description']) ?>"
                                data-amount="<?= $exp['amount'] ?>"
                                data-category="<?= $exp['category'] ?>"
                                data-date="<?= $exp['expense_date'] ?? '' ?>">Επεξεργασία</button>
                            <form method="POST" action="<?= APP_URL ?>/admin/financials/expenses/<?= $exp['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Διαγραφή εξόδου;')">
                                <?= CSRF::field() ?><button class="btn btn-sm btn-outline-danger">Διαγραφή</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                <?php if(empty($data)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Δεν βρέθηκαν έξοδα.</td></tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(($last_page ?? 1) > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for($p=1;$p<=$last_page;$p++): ?>
    <li class="page-item <?= $p==($current_page??1)?'active':'' ?>"><a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $p ?></a></li>
    <?php endfor ?>
</ul></nav>
<?php endif ?>

<!-- Modal Προσθήκης Εξόδου -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/admin/financials/expenses" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Προσθήκη Εξόδου</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Περιγραφή *</label><input type="text" name="description" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">Ποσό (€) *</label><input type="number" name="amount" class="form-control" step="0.01" required min="0.01"></div>
                        <div class="col-6">
                            <label class="form-label">Κατηγορία</label>
                            <select name="category" class="form-select">
                                <?php foreach (['hosting','software','hardware','subcontractor','marketing','salary','tax','other'] as $cat): ?>
                                <option value="<?= $cat ?>"><?= grExpCat($cat) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label">Ημερομηνία</label><input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                        <div class="col-6"><label class="form-label">Απόδειξη (προαιρετικά)</label><input type="file" name="receipt_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                        <div class="col-6"><label class="form-label">ID Έργου</label><input type="number" name="project_id" class="form-control" placeholder="Προαιρετικό"></div>
                        <div class="col-6"><label class="form-label">ID Συμφωνίας</label><input type="number" name="deal_id" class="form-control" placeholder="Προαιρετικό"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Προσθήκη Εξόδου</button></div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Επεξεργασίας Εξόδου -->
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editExpenseForm" action="">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Επεξεργασία Εξόδου</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Περιγραφή *</label><input type="text" name="description" id="editDescription" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">Ποσό (€) *</label><input type="number" name="amount" id="editAmount" class="form-control" step="0.01" required></div>
                        <div class="col-6">
                            <label class="form-label">Κατηγορία</label>
                            <select name="category" id="editCategory" class="form-select">
                                <?php foreach (['hosting','software','hardware','subcontractor','marketing','salary','tax','other'] as $cat): ?>
                                <option value="<?= $cat ?>"><?= grExpCat($cat) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label">Ημερομηνία</label><input type="date" name="expense_date" id="editDate" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Αποθήκευση Αλλαγών</button></div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editExpenseModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id  = btn.dataset.id;
    document.getElementById('editExpenseForm').action = '<?= APP_URL ?>/admin/financials/expenses/' + id + '/update';
    document.getElementById('editDescription').value  = btn.dataset.description;
    document.getElementById('editAmount').value        = btn.dataset.amount;
    document.getElementById('editDate').value          = btn.dataset.date;
    const catSel = document.getElementById('editCategory');
    for (let opt of catSel.options) { opt.selected = opt.value === btn.dataset.category; }
});
</script>
