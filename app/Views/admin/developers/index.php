<?php use App\Core\CSRF; ?>
<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <div class="d-flex gap-2 align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση προγραμματιστών..." value="<?= htmlspecialchars($search ?? '') ?>">
            <button class="btn btn-sm btn-outline-secondary">Αναζήτηση</button>
        </form>
        <button type="button" class="btn btn-danger btn-sm d-none" id="bulkDeleteBtn">
            <i class="bi bi-trash me-1"></i>Διαγραφή Επιλεγμένων (<span id="bulkCount">0</span>)
        </button>
    </div>
    <a href="<?= APP_URL ?>/admin/developers/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Προσθήκη Προγραμματιστή</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:36px"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                        <th>Ονοματεπώνυμο</th><th>Email</th><th>Τηλέφωνο</th><th>Έργα</th><th>Σε Εξέλιξη</th><th>Ολοκληρωμένα</th><th>Κέρδη</th><th>Κατάσταση</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $dev): ?>
                    <?php $st = $dev['stats'] ?? []; ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input row-check" value="<?= $dev['id'] ?>"></td>
                        <td class="fw-semibold"><?= htmlspecialchars($dev['name']) ?></td>
                        <td><?= htmlspecialchars($dev['email']) ?></td>
                        <td><?= htmlspecialchars($dev['phone'] ?? '—') ?></td>
                        <td><?= $st['total_projects'] ?? 0 ?></td>
                        <td><span class="badge bg-primary"><?= $st['in_progress'] ?? 0 ?></span></td>
                        <td><span class="badge bg-success"><?= $st['completed'] ?? 0 ?></span></td>
                        <td class="text-success fw-semibold">€<?= number_format($st['commission_earned'] ?? 0, 2) ?></td>
                        <td>
                            <span class="badge <?= $dev['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $dev['is_active'] ? 'Ενεργός' : 'Ανενεργός' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/admin/developers/<?= $dev['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Επεξεργασία</a>
                            <form method="POST" action="<?= APP_URL ?>/admin/developers/<?= $dev['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Διαγραφή αυτού του προγραμματιστή;')">
                                <?= \App\Core\CSRF::field() ?>
                                <button class="btn btn-sm btn-outline-danger">Διαγραφή</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                <?php if(empty($data)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">Δεν βρέθηκαν προγραμματιστές.</td></tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(($last_page ?? 1) > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for($p=1;$p<=$last_page;$p++): ?>
    <li class="page-item <?= $p==($current_page??1)?'active':'' ?>"><a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search??'') ?>"><?= $p ?></a></li>
    <?php endfor ?>
</ul></nav>
<?php endif ?>
<form id="bulkDeleteForm" method="POST" action="<?= APP_URL ?>/admin/developers/bulk-delete" class="d-none">
    <?= CSRF::field() ?>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initBulkDelete('.row-check', 'checkAll', 'bulkCount', 'bulkDeleteBtn', 'bulkDeleteForm',
        'Διαγραφή {n} προγραμματιστών; Η ενέργεια είναι μόνιμη.');
});
</script>
