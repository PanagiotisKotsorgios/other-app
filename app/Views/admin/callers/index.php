<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>
<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-danger btn-sm d-none" id="bulkDeleteBtn">
            <i class="bi bi-trash me-1"></i>Διαγραφή Επιλεγμένων (<span id="bulkCount">0</span>)
        </button>
    </div>
    <a href="<?= APP_URL ?>/admin/callers/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Προσθήκη Τηλεφωνητή</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση ονόματος ή email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Αναζήτηση</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:36px"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                    <th>Ονοματεπώνυμο</th><th>Email</th><th>Τηλέφωνο</th><th>Κατάσταση</th><th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $c): ?>
            <tr>
                <td><input type="checkbox" class="form-check-input row-check" value="<?= $c['id'] ?>"></td>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['phone']??'—') ?></td>
                <td><span class="badge <?= $c['is_active']?'bg-success':'bg-secondary' ?>"><?= $c['is_active']?'Ενεργός':'Ανενεργός' ?></span></td>
                <td class="d-flex gap-1">
                    <a href="<?= APP_URL ?>/admin/callers/<?= $c['id'] ?>/stats" class="btn btn-xs btn-outline-info"><i class="bi bi-bar-chart"></i></a>
                    <a href="<?= APP_URL ?>/admin/callers/<?= $c['id'] ?>/edit" class="btn btn-xs btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <a href="<?= APP_URL ?>/admin/messages/compose?to=<?= $c['id'] ?>" class="btn btn-xs btn-outline-secondary"><i class="bi bi-envelope"></i></a>
                    <form method="POST" action="<?= APP_URL ?>/admin/callers/<?= $c['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Διαγραφή αυτού του τηλεφωνητή;')">
                        <?= CSRF::field() ?>
                        <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="6" class="text-center py-4 text-muted">Δεν βρέθηκαν τηλεφωνητές.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page > 1): ?><div class="card-footer bg-white"><?php include __DIR__ . '/../../_partials/pagination.php' ?></div><?php endif ?>
</div>
<form id="bulkDeleteForm" method="POST" action="<?= APP_URL ?>/admin/callers/bulk-delete" class="d-none">
    <?= CSRF::field() ?>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initBulkDelete('.row-check', 'checkAll', 'bulkCount', 'bulkDeleteBtn', 'bulkDeleteForm',
        'Διαγραφή {n} τηλεφωνητών; Θα διαγραφούν επίσης τα deals και οι προμήθειές τους. Η ενέργεια είναι μόνιμη.');
});
</script>
