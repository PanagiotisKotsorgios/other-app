<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>
<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <div></div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/admin/businesses/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Προσθήκη Επιχείρησης</a>
        <a href="<?= APP_URL ?>/admin/import" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Εισαγωγή Excel</a>
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkModal"><i class="bi bi-people me-1"></i>Μαζική Ανάθεση</button>
        <button type="button" class="btn btn-danger btn-sm d-none" id="bulkDeleteBtn"><i class="bi bi-trash me-1"></i>Διαγραφή Επιλεγμένων (<span id="bulkCount">0</span>)</button>
    </div>
</div>

<!-- Φίλτρα -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση..." value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div class="col-md-2">
                <select name="city" class="form-select form-select-sm">
                    <option value="">Όλες οι Πόλεις</option>
                    <?php foreach ($cities as $c): ?><option value="<?= htmlspecialchars($c) ?>" <?= $filters['city']===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option><?php endforeach ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select form-select-sm">
                    <option value="">Όλες οι Κατηγορίες</option>
                    <?php foreach ($cats as $c): ?><option value="<?= htmlspecialchars($c) ?>" <?= $filters['category']===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option><?php endforeach ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Όλες οι Καταστάσεις</option>
                    <?php foreach (['new','contacted','interested','not_interested','deal_closed','follow_up'] as $s): ?>
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
            <div class="col-md-1">
                <button class="btn btn-primary btn-sm w-100">Φίλτρο</button>
            </div>
        </form>
    </div>
</div>

<!-- Πίνακας -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <form id="bulkForm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th><input type="checkbox" id="checkAll" class="form-check-input"></th>
                    <th>Εταιρία</th><th>Επαφή</th><th>Πόλη</th><th>Κατηγορία</th><th>Κατάσταση</th><th>Ανατεθημένο Σε</th><th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $b): ?>
            <tr>
                <td><input type="checkbox" name="business_ids[]" value="<?= $b['id'] ?>" class="form-check-input biz-check"></td>
                <td>
                    <a href="<?= APP_URL ?>/admin/businesses/<?= $b['id'] ?>" class="fw-semibold text-decoration-none">
                        <?= htmlspecialchars($b['company_name']) ?>
                    </a>
                    <?php if($b['website']): ?><br><a href="<?= htmlspecialchars($b['website']) ?>" target="_blank" class="text-muted small"><?= htmlspecialchars($b['website']) ?></a><?php endif ?>
                </td>
                <td>
                    <?= htmlspecialchars($b['contact_name'] ?? '') ?><br>
                    <a href="mailto:<?= htmlspecialchars($b['email'] ?? '') ?>" class="text-muted small"><?= htmlspecialchars($b['email'] ?? '') ?></a>
                </td>
                <td><?= htmlspecialchars($b['city'] ?? '') ?></td>
                <td><?= htmlspecialchars($b['category'] ?? '') ?></td>
                <td><span class="badge <?= statusBadge($b['status']) ?>"><?= grStatus($b['status']) ?></span></td>
                <td><?= $b['assigned_caller'] ? htmlspecialchars($b['assigned_caller']) : '<em class="text-muted">Μη ανατεθημένο</em>' ?></td>
                <td>
                    <a href="<?= APP_URL ?>/admin/businesses/<?= $b['id'] ?>/edit" class="btn btn-xs btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="<?= APP_URL ?>/admin/businesses/<?= $b['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Διαγραφή αυτής της επιχείρησης;')">
                        <?= CSRF::field() ?>
                        <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="8" class="text-center py-4 text-muted">Δεν βρέθηκαν επιχειρήσεις.</td></tr><?php endif ?>
            </tbody>
        </table>
        </form>
    </div>
    <?php if ($last_page > 1): ?>
    <div class="card-footer bg-white">
        <?php include __DIR__ . '/../../_partials/pagination.php' ?>
    </div>
    <?php endif ?>
</div>

<!-- Μαζική Ανάθεση Modal -->
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= APP_URL ?>/admin/businesses/bulk-assign">
                <?= CSRF::field() ?>
                <div class="modal-header"><h5 class="modal-title">Μαζική Ανάθεση Επιχειρήσεων</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div id="selectedIds"></div>
                    <div class="mb-3">
                        <label class="form-label">Ανάθεση Σε</label>
                        <select name="caller_id" class="form-select" required>
                            <option value="">Επιλογή Τηλεφωνητή</option>
                            <?php foreach ($callers as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Τρόπος Ανάθεσης</label>
                        <select name="assign_mode" class="form-select" id="assignMode">
                            <option value="manual">Μόνο επιλεγμένες επιχειρήσεις</option>
                            <option value="random">Τυχαία (μη ανατεθημένες)</option>
                        </select>
                    </div>
                    <div id="randomQtyDiv" class="d-none mb-3">
                        <label class="form-label">Αριθμός επιχειρήσεων για τυχαία ανάθεση</label>
                        <input type="number" name="random_qty" class="form-control" value="10" min="1" max="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                    <button type="submit" class="btn btn-primary">Ανάθεση</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
function statusBadge(string $status): string {
    return match($status) {
        'new'           => 'bg-secondary',
        'contacted'     => 'bg-info text-dark',
        'interested'    => 'bg-primary',
        'not_interested'=> 'bg-danger',
        'deal_closed'   => 'bg-success',
        'follow_up'     => 'bg-warning text-dark',
        default         => 'bg-secondary',
    };
}
?>
<script>
const checkAll  = document.getElementById('checkAll');
const countEl   = document.getElementById('bulkCount');
const deleteBtn = document.getElementById('bulkDeleteBtn');

function updateBulkBar() {
    const n = document.querySelectorAll('.biz-check:checked').length;
    countEl.textContent = n;
    deleteBtn.classList.toggle('d-none', n === 0);
}
checkAll?.addEventListener('change', function() {
    document.querySelectorAll('.biz-check').forEach(c => c.checked = this.checked);
    updateBulkBar();
});
document.querySelectorAll('.biz-check').forEach(c => c.addEventListener('change', updateBulkBar));

document.getElementById('assignMode')?.addEventListener('change', function() {
    document.getElementById('randomQtyDiv').classList.toggle('d-none', this.value !== 'random');
});
document.querySelector('[data-bs-target="#bulkModal"]')?.addEventListener('click', function() {
    const container = document.getElementById('selectedIds');
    container.innerHTML = '';
    document.querySelectorAll('.biz-check:checked').forEach(c => {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = 'business_ids[]'; i.value = c.value;
        container.appendChild(i);
    });
});

deleteBtn?.addEventListener('click', function() {
    const checked = document.querySelectorAll('.biz-check:checked');
    if (!checked.length) return;
    if (!confirm('Διαγραφή ' + checked.length + ' επιχειρήσεων; Θα διαγραφούν επίσης οι συμφωνίες και αναθέσεις τους. Η ενέργεια είναι μόνιμη.')) return;
    const form = document.getElementById('bulkDeleteForm');
    checked.forEach(c => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = c.value;
        form.appendChild(inp);
    });
    form.submit();
});
</script>
<form id="bulkDeleteForm" method="POST" action="<?= APP_URL ?>/admin/businesses/bulk-delete" class="d-none">
    <?= CSRF::field() ?>
</form>
