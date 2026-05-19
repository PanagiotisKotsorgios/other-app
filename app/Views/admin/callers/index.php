<?php use App\Core\CSRF; ?>
<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <div></div>
    <a href="<?= APP_URL ?>/admin/callers/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Caller</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Search</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['phone']??'—') ?></td>
                <td><span class="badge <?= $c['is_active']?'bg-success':'bg-secondary' ?>"><?= $c['is_active']?'Active':'Inactive' ?></span></td>
                <td class="d-flex gap-1">
                    <a href="<?= APP_URL ?>/admin/callers/<?= $c['id'] ?>/stats" class="btn btn-xs btn-outline-info"><i class="bi bi-bar-chart"></i></a>
                    <a href="<?= APP_URL ?>/admin/callers/<?= $c['id'] ?>/edit" class="btn btn-xs btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <a href="<?= APP_URL ?>/admin/messages/compose?to=<?= $c['id'] ?>" class="btn btn-xs btn-outline-secondary"><i class="bi bi-envelope"></i></a>
                    <form method="POST" action="<?= APP_URL ?>/admin/callers/<?= $c['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this caller?')">
                        <?= CSRF::field() ?>
                        <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No callers found.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page > 1): ?><div class="card-footer bg-white"><?php include __DIR__ . '/../../_partials/pagination.php' ?></div><?php endif ?>
</div>
