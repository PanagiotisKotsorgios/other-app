<?php require_once __DIR__ . '/../../_partials/gr_helpers.php'; ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση..." value="<?= htmlspecialchars($filters['search']) ?>"></div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Όλες οι Καταστάσεις</option>
                    <?php foreach(['new','contacted','interested','not_interested','deal_closed','follow_up'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= grStatus($s) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Φίλτρο</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Οι Επιχειρήσεις μου</span>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary"><?= $total ?> σύνολο</span>
            <a href="<?= APP_URL ?>/caller/businesses/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Νέο Lead
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Εταιρία</th><th>Επαφή</th><th>Τηλέφωνο</th><th>Πόλη</th><th>Κατηγορία</th><th>Κατάσταση</th><th>Ενέργειες</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $b): ?>
            <tr>
                <td>
                    <a href="<?= APP_URL ?>/caller/businesses/<?= $b['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($b['company_name']) ?></a>
                    <?php if($b['website']): ?><br><a href="<?= htmlspecialchars($b['website']) ?>" target="_blank" class="text-muted small"><?= htmlspecialchars($b['website']) ?></a><?php endif ?>
                </td>
                <td>
                    <?= htmlspecialchars($b['contact_name']??'') ?>
                    <?php if($b['email']): ?><br><a href="mailto:<?= htmlspecialchars($b['email']) ?>" class="text-muted small"><?= htmlspecialchars($b['email']) ?></a><?php endif ?>
                </td>
                <td><?= htmlspecialchars($b['phone']??'—') ?></td>
                <td><?= htmlspecialchars($b['city']??'—') ?></td>
                <td><?= htmlspecialchars($b['category']??'—') ?></td>
                <td><span class="badge bg-secondary"><?= grStatus($b['status']) ?></span></td>
                <td>
                    <a href="<?= APP_URL ?>/caller/businesses/<?= $b['id'] ?>" class="btn btn-xs btn-primary"><i class="bi bi-eye"></i></a>
                    <a href="<?= APP_URL ?>/caller/deals/create/<?= $b['id'] ?>" class="btn btn-xs btn-success" title="Υποβολή Συμφωνίας"><i class="bi bi-bag-plus"></i></a>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-building fs-2 d-block mb-2 opacity-25"></i>
                Δεν υπάρχουν επιχειρήσεις ακόμα.<br>
                <a href="<?= APP_URL ?>/caller/businesses/create" class="btn btn-primary btn-sm mt-3">
                    <i class="bi bi-plus-lg me-1"></i>Προσθήκη Lead
                </a>
            </td></tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/../../_partials/pagination.php' ?></div><?php endif ?>
</div>
