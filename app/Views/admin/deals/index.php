<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search company or caller..." value="<?= htmlspecialchars($filters['search']) ?>"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending','approved','rejected','in_progress','completed'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="caller_id" class="form-select form-select-sm">
                    <option value="">All Callers</option>
                    <?php foreach ($callers as $c): ?><option value="<?= $c['id'] ?>" <?= $filters['caller_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option><?php endforeach ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Business</th><th>Caller</th><th>Service</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $d): ?>
            <tr>
                <td class="text-muted small">#<?= $d['id'] ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($d['company_name']) ?></td>
                <td><?= htmlspecialchars($d['caller_name']) ?></td>
                <td><?= htmlspecialchars($d['service_name']??'—') ?></td>
                <td class="fw-semibold">€<?= number_format($d['amount'],2) ?></td>
                <td><span class="badge <?= dealBadge($d['status']) ?>"><?= ucfirst(str_replace('_',' ',$d['status'])) ?></span></td>
                <td class="small text-muted"><?= date('d M Y', strtotime($d['created_at'])) ?></td>
                <td><a href="<?= APP_URL ?>/admin/deals/<?= $d['id'] ?>" class="btn btn-xs btn-outline-primary">View</a></td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="8" class="text-center py-4 text-muted">No deals found.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/../../_partials/pagination.php' ?></div><?php endif ?>
</div>

<?php function dealBadge(string $s): string {
    return match($s) { 'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark', default=>'bg-secondary' };
} ?>
