<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="<?= htmlspecialchars($filters['search']) ?>"></div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach(['new','contacted','interested','not_interested','deal_closed','follow_up'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Assigned Businesses</span>
        <span class="badge bg-primary"><?= $total ?> total</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Company</th><th>Contact</th><th>Phone</th><th>City</th><th>Category</th><th>Status</th><th>Actions</th></tr>
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
                <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
                <td>
                    <a href="<?= APP_URL ?>/caller/businesses/<?= $b['id'] ?>" class="btn btn-xs btn-primary"><i class="bi bi-eye"></i></a>
                    <a href="<?= APP_URL ?>/caller/deals/create/<?= $b['id'] ?>" class="btn btn-xs btn-success" title="Submit Deal"><i class="bi bi-bag-plus"></i></a>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No businesses assigned to you.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/../../_partials/pagination.php' ?></div><?php endif ?>
</div>
