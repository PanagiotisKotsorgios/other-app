<?php require_once __DIR__ . '/../../_partials/gr_helpers.php'; use App\Core\CSRF; ?>
<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <div class="d-flex gap-2 align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση συνεργατών..." value="<?= htmlspecialchars($search ?? '') ?>">
            <button class="btn btn-sm btn-outline-secondary">Αναζήτηση</button>
        </form>
        <button type="button" class="btn btn-danger btn-sm d-none" id="bulkDeleteBtn">
            <i class="bi bi-trash me-1"></i>Διαγραφή Επιλεγμένων (<span id="bulkCount">0</span>)
        </button>
    </div>
    <a href="<?= APP_URL ?>/admin/partners/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Προσθήκη Συνεργάτη</a>
</div>

<?php
$catColorMap = [
    'green'  => ['bg' => '#dcfce7', 'txt' => '#166534'],
    'blue'   => ['bg' => '#dbeafe', 'txt' => '#1d4ed8'],
    'orange' => ['bg' => '#fff7ed', 'txt' => '#c2410c'],
    'red'    => ['bg' => '#fee2e2', 'txt' => '#b91c1c'],
    'purple' => ['bg' => '#f3e8ff', 'txt' => '#7e22ce'],
    'teal'   => ['bg' => '#ccfbf1', 'txt' => '#0f766e'],
];
?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:36px"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                        <th>Ονοματεπώνυμο</th><th>Email</th><th>Κατηγορία</th><th>Ρόλοι</th><th>Παραπομπές</th><th>Έσοδα</th><th>Εκκρεμής Προμήθεια</th><th>Κατάσταση</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $partner): ?>
                    <?php
                    $st     = $partner['stats'] ?? [];
                    $color  = $catColorMap[$partner['cat_color'] ?? ''] ?? null;
                    $allRoles = array_filter(explode(',', $partner['all_roles'] ?? ''));
                    ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input row-check" value="<?= $partner['id'] ?>"></td>
                        <td class="fw-semibold"><?= htmlspecialchars($partner['name']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars($partner['email']) ?></td>
                        <td>
                            <?php if(!empty($partner['cat_name']) && $color): ?>
                            <span class="badge fw-700 px-2"
                                  style="background:<?= $color['bg'] ?>;color:<?= $color['txt'] ?>;border:1px solid <?= $color['txt'] ?>30"
                                  title="<?= htmlspecialchars($partner['cat_label'] ?? '') ?>">
                                <?= htmlspecialchars($partner['cat_name']) ?>
                            </span>
                            <span class="text-muted small ms-1"><?= number_format($partner['partner_rate'] ?? 0, 0) ?>%</span>
                            <?php elseif(!empty($partner['cat_name'])): ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($partner['cat_name']) ?></span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif ?>
                        </td>
                        <td>
                            <?php foreach($allRoles as $r): ?>
                            <span class="badge <?= match($r){'partner'=>'bg-success','developer'=>'bg-primary','caller'=>'bg-info text-dark','admin'=>'bg-danger',default=>'bg-secondary'} ?> me-1">
                                <?= grRole($r) ?>
                            </span>
                            <?php endforeach ?>
                        </td>
                        <td><?= $st['total_referrals'] ?? 0 ?></td>
                        <td class="text-success fw-semibold">€<?= number_format($st['revenue_generated'] ?? 0, 2) ?></td>
                        <td class="text-warning fw-semibold">€<?= number_format($st['commission_owed'] ?? 0, 2) ?></td>
                        <td>
                            <span class="badge <?= $partner['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $partner['is_active'] ? 'Ενεργός' : 'Ανενεργός' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/admin/partners/<?= $partner['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Επεξεργασία</a>
                            <form method="POST" action="<?= APP_URL ?>/admin/partners/<?= $partner['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Διαγραφή αυτού του συνεργάτη;')">
                                <?= \App\Core\CSRF::field() ?>
                                <button class="btn btn-sm btn-outline-danger">Διαγραφή</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                <?php if(empty($data)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">Δεν βρέθηκαν συνεργάτες.</td></tr>
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
<form id="bulkDeleteForm" method="POST" action="<?= APP_URL ?>/admin/partners/bulk-delete" class="d-none">
    <?= CSRF::field() ?>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initBulkDelete('.row-check', 'checkAll', 'bulkCount', 'bulkDeleteBtn', 'bulkDeleteForm',
        'Διαγραφή {n} συνεργατών; Η ενέργεια είναι μόνιμη.');
});
</script>
