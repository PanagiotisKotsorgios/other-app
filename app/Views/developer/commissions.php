<!-- E:\call_center\app\Views\developer\commissions.php -->
<div class="card border-0 shadow-sm mt-2">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-currency-euro me-1 text-success"></i> My Commissions (Developer)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Business</th><th>Project</th><th>Deal Amount</th><th>Commission</th><th>Rate</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php foreach ($data as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['company_name']) ?></td>
                        <td><?= htmlspecialchars($c['project_title'] ?? '—') ?></td>
                        <td>€<?= number_format($c['deal_amount'],2) ?></td>
                        <td class="fw-bold text-success">€<?= number_format($c['amount'],2) ?></td>
                        <td><?= $c['rate'] ?>%</td>
                        <td><span class="badge <?= $c['is_paid']?'bg-success':'bg-warning text-dark' ?>"><?= $c['is_paid']?'Paid':'Pending' ?></span></td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    </tr>
                <?php endforeach ?>
                <?php if(empty($data)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No commissions yet.</td></tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(($last_page ?? 1) > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for($p=1;$p<=$last_page;$p++): ?>
    <li class="page-item <?= $p==($current_page??1)?'active':'' ?>"><a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a></li>
    <?php endfor ?>
</ul></nav>
<?php endif ?>
