<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>

<!-- Φίλτρο -->
<form method="GET" class="row g-2 mb-3 mt-1">
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">Όλες οι Καταστάσεις</option>
            <?php foreach (['pending','approved','in_progress','completed','rejected'] as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= grStatus($s) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Φίλτρο</button></div>
    <div class="col-auto ms-auto">
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#submitReferralModal">
            <i class="bi bi-plus-lg me-1"></i>Υποβολή Παραπομπής
        </button>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Επιχείρηση</th><th>Ποσό Συμφωνίας</th><th>Κατάσταση</th><th>Προμήθεια</th><th>Κατάσταση Προμήθειας</th><th>Ημερομηνία</th></tr>
                </thead>
                <tbody>
                <?php foreach ($data as $deal): ?>
                    <?php
                    $comm = $commByDeal[$deal['id']] ?? null;
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($deal['company_name']) ?></td>
                        <td>€<?= number_format($deal['amount'],2) ?></td>
                        <td>
                            <span class="badge <?= match($deal['status']){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>">
                                <?= grStatus($deal['status']) ?>
                            </span>
                        </td>
                        <td class="text-success fw-semibold">
                            <?php if($comm): ?>
                            €<?= number_format($comm['amount'],2) ?> (<?= $comm['rate'] ?>%)
                            <?php elseif(in_array($deal['status'],['approved','in_progress','completed'])): ?>
                            €<?= number_format($deal['amount']*($partnerRate??20)/100,2) ?> (<?= number_format($partnerRate??20,1) ?>%)
                            <?php else: ?>
                            <span class="text-muted">Αναμονή έγκρισης</span>
                            <?php endif ?>
                        </td>
                        <td>
                            <?php if($comm): ?>
                            <span class="badge <?= $comm['is_paid']?'bg-success':'bg-warning text-dark' ?>"><?= $comm['is_paid']?'Πληρωμένη':'Εκκρεμής' ?></span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif ?>
                        </td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($deal['created_at'])) ?></td>
                    </tr>
                <?php endforeach ?>
                <?php if(empty($data)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Δεν έχουν υποβληθεί παραπομπές.</td></tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(($last_page ?? 1) > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for($p=1;$p<=$last_page;$p++): ?>
    <li class="page-item <?= $p==($current_page??1)?'active':'' ?>"><a class="page-link" href="?page=<?= $p ?>&status=<?= urlencode($filters['status']??'') ?>"><?= $p ?></a></li>
    <?php endfor ?>
</ul></nav>
<?php endif ?>

<!-- Modal Υποβολής Παραπομπής -->
<div class="modal fade" id="submitReferralModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/partner/referrals/submit">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Υποβολή Παραπομπής</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Επωνυμία Εταιρίας *</label><input type="text" name="company_name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Τηλέφωνο</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Πόλη</label><input type="text" name="city" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Εκτιμ. Αξία (€) *</label><input type="number" name="amount" class="form-control" step="0.01" required min="1"></div>
                        <div class="col-12"><label class="form-label">Σημειώσεις</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Υποβολή</button></div>
            </div>
        </form>
    </div>
</div>
