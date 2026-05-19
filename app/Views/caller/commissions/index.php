<!-- Summary -->
<div class="row g-3 mb-3 mt-1">
    <div class="col-md-4"><div class="kpi-card kpi-blue"><div class="kpi-icon"><i class="bi bi-currency-euro"></i></div><div class="kpi-value">€<?= number_format($summary['total']??0,2) ?></div><div class="kpi-label">Total Earned</div></div></div>
    <div class="col-md-4"><div class="kpi-card kpi-green"><div class="kpi-icon"><i class="bi bi-check-circle"></i></div><div class="kpi-value">€<?= number_format($summary['paid']??0,2) ?></div><div class="kpi-label">Received</div></div></div>
    <div class="col-md-4"><div class="kpi-card kpi-red"><div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div><div class="kpi-value">€<?= number_format($summary['owed']??0,2) ?></div><div class="kpi-label">Pending Payment</div></div></div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">My Commissions</div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Business</th><th>Service</th><th>Deal Amount</th><th>Commission</th><th>Rate</th><th>Status</th><th>Paid At</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $c): ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($c['company_name']) ?></td>
                <td><?= htmlspecialchars($c['service_name']??'—') ?></td>
                <td>€<?= number_format($c['deal_amount'],2) ?></td>
                <td class="fw-bold text-success">€<?= number_format($c['amount'],2) ?></td>
                <td><?= $c['rate'] ?>%</td>
                <td><span class="badge <?= $c['is_paid']?'bg-success':'bg-warning text-dark' ?>"><?= $c['is_paid']?'Paid':'Pending' ?></span></td>
                <td class="small text-muted"><?= $c['paid_at'] ? date('d M Y', strtotime($c['paid_at'])) : '—' ?></td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No commissions yet.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/../../_partials/pagination.php' ?></div><?php endif ?>
</div>
