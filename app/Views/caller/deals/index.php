<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="<?= htmlspecialchars($filters['search']) ?>"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach(['pending','approved','rejected','in_progress','completed'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between">
        <span>My Deals</span><span class="badge bg-primary"><?= $total ?></span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Business</th><th>Service</th><th>Amount</th><th>Commission (<?= COMMISSION_RATE ?>%)</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $d): ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($d['company_name']) ?></td>
                <td><?= htmlspecialchars($d['service_name']??'—') ?></td>
                <td class="fw-bold">€<?= number_format($d['amount'],2) ?></td>
                <td class="text-success fw-semibold">€<?= number_format($d['amount']*COMMISSION_RATE/100,2) ?></td>
                <td><span class="badge <?= match($d['status']){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>"><?= ucfirst(str_replace('_',' ',$d['status'])) ?></span></td>
                <td class="small text-muted"><?= date('d M Y', strtotime($d['created_at'])) ?></td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No deals submitted yet.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/../../_partials/pagination.php' ?></div><?php endif ?>
</div>
