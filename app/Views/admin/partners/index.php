<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση συνεργατών..." value="<?= htmlspecialchars($search ?? '') ?>">
        <button class="btn btn-sm btn-outline-secondary">Αναζήτηση</button>
    </form>
    <a href="<?= APP_URL ?>/admin/partners/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Προσθήκη Συνεργάτη</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Ονοματεπώνυμο</th><th>Email</th><th>Τηλέφωνο</th><th>Παραπομπές</th><th>Έσοδα</th><th>Κερδισμένη Προμήθεια</th><th>Οφειλόμενη Προμήθεια</th><th>Κατάσταση</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($data as $partner): ?>
                    <?php $st = $partner['stats'] ?? []; ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($partner['name']) ?></td>
                        <td><?= htmlspecialchars($partner['email']) ?></td>
                        <td><?= htmlspecialchars($partner['phone'] ?? '—') ?></td>
                        <td><?= $st['total_referrals'] ?? 0 ?></td>
                        <td class="text-success fw-semibold">€<?= number_format($st['revenue_generated'] ?? 0, 2) ?></td>
                        <td>€<?= number_format($st['commission_earned'] ?? 0, 2) ?></td>
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
                    <tr><td colspan="9" class="text-center text-muted py-4">Δεν βρέθηκαν συνεργάτες.</td></tr>
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
