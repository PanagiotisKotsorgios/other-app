<?php
use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';

$involvementInfo = [
    'contact'        => ['label'=>'Απλή Επαφή',          'desc'=>'Έδωσα μόνο τα στοιχεία επικοινωνίας', 'rate'=>10, 'icon'=>'telephone', 'color'=>'info'],
    'presentation'   => ['label'=>'Παρουσίαση',           'desc'=>'Παρουσίασα τις υπηρεσίες στον πελάτη', 'rate'=>13, 'icon'=>'easel',    'color'=>'primary'],
    'active_support' => ['label'=>'Ενεργή Υποστήριξη',    'desc'=>'Βοήθησα ενεργά στη διαπραγμάτευση',   'rate'=>16, 'icon'=>'people',    'color'=>'warning'],
    'full_closure'   => ['label'=>'Πλήρες Κλείσιμο',      'desc'=>'Έκλεισα το deal εξ ολοκλήρου μόνος μου','rate'=>20,'icon'=>'trophy',   'color'=>'success'],
];
$autoOpen = !empty($_GET['open']);
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
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#submitReferralModal">
            <i class="bi bi-plus-lg me-1"></i>Νέα Παραπομπή
        </button>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Επιχείρηση</th><th>Ποσό</th><th>Συμμετοχή</th><th>Κατάσταση</th><th>Προμήθεια</th><th>Πληρωμή</th><th>Ημερομηνία</th></tr>
                </thead>
                <tbody>
                <?php foreach ($data as $deal): ?>
                    <?php $comm = $commByDeal[$deal['id']] ?? null; ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($deal['company_name']) ?></td>
                        <td>€<?= number_format($deal['amount'],2) ?></td>
                        <td>
                            <?php if(!empty($deal['partner_involvement']) && isset($involvementInfo[$deal['partner_involvement']])): ?>
                            <?php $inv = $involvementInfo[$deal['partner_involvement']]; ?>
                            <span class="badge bg-<?= $inv['color'] ?> text-<?= $inv['color']==='warning'?'dark':'white' ?>">
                                <i class="bi bi-<?= $inv['icon'] ?> me-1"></i><?= $inv['label'] ?>
                            </span>
                            <span class="text-muted small ms-1">~<?= $inv['rate'] ?>%</span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif ?>
                        </td>
                        <td>
                            <span class="badge <?= match($deal['status']){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>">
                                <?= grStatus($deal['status']) ?>
                            </span>
                        </td>
                        <td class="text-success fw-semibold">
                            <?php if($comm): ?>
                            €<?= number_format($comm['amount'],2) ?> (<?= $comm['rate'] ?>%)
                            <?php elseif(in_array($deal['status'],['approved','in_progress','completed'])): ?>
                            <?php
                            $invRate  = $involvementInfo[$deal['partner_involvement'] ?? '']['rate'] ?? null;
                            $baseRate = $partnerRate ?? 20;
                            $dispRate = $invRate !== null ? max($baseRate, $invRate) : $baseRate;
                            ?>
                            €<?= number_format($deal['amount']*$dispRate/100,2) ?> (<?= number_format($dispRate,1) ?>%)
                            <?php else: ?>
                            <span class="text-muted small">Αναμονή έγκρισης</span>
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
                    <tr><td colspan="7" class="text-center text-muted py-4">Δεν έχουν υποβληθεί παραπομπές.</td></tr>
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
    <div class="modal-dialog modal-lg">
        <form method="POST" action="<?= APP_URL ?>/partner/referrals/submit">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-share me-2"></i>Νέα Παραπομπή</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Επίπεδο Συμμετοχής -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Επίπεδο Συμμετοχής σας *</label>
                        <div class="row g-2">
                            <?php foreach ($involvementInfo as $key => $inv): ?>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" name="partner_involvement"
                                       id="inv_<?= $key ?>" value="<?= $key ?>"
                                       <?= $key === 'contact' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-<?= $inv['color'] ?> w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3 text-center"
                                       for="inv_<?= $key ?>" style="min-height:110px">
                                    <i class="bi bi-<?= $inv['icon'] ?> fs-3 mb-1"></i>
                                    <span class="fw-600 small"><?= $inv['label'] ?></span>
                                    <span class="badge bg-<?= $inv['color'] ?> mt-1"><?= $inv['rate'] ?>%</span>
                                    <span class="text-muted mt-1" style="font-size:.68rem;line-height:1.2"><?= $inv['desc'] ?></span>
                                </label>
                            </div>
                            <?php endforeach ?>
                        </div>
                        <div class="form-text mt-1">Το τελικό ποσοστό εγκρίνεται από τον διαχειριστή. Ισχύει το μέγιστο μεταξύ επιπέδου συμμετοχής και κατηγορίας σας (<?= number_format($partnerRate??20,1) ?>%).</div>
                    </div>

                    <hr>

                    <!-- Στοιχεία Εταιρίας -->
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Επωνυμία Εταιρίας *</label>
                            <input type="text" name="company_name" class="form-control" required placeholder="Όνομα επιχείρησης">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τηλέφωνο</label>
                            <input type="text" name="phone" class="form-control" placeholder="+30...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Πόλη</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Εκτιμ. Αξία Συμφωνίας (€) *</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required min="1" id="referralAmount">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Σημειώσεις</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Οποιεσδήποτε πρόσθετες πληροφορίες..."></textarea>
                        </div>
                    </div>

                    <!-- Εκτίμηση Προμήθειας -->
                    <div id="commissionPreview" class="alert alert-success mt-3 mb-0 d-none">
                        <i class="bi bi-calculator me-1"></i>
                        Εκτιμώμενη προμήθεια: <strong id="commissionAmt"></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Υποβολή Παραπομπής</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if($autoOpen): ?>
<script>document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('submitReferralModal')).show();
});</script>
<?php endif ?>

<script>
(function() {
    var rates = {contact:10, presentation:13, active_support:16, full_closure:20};
    var catRate = <?= (float)($partnerRate ?? 20) ?>;
    var amtEl   = document.getElementById('referralAmount');
    var preview = document.getElementById('commissionPreview');
    var amtOut  = document.getElementById('commissionAmt');

    function update() {
        var inv = document.querySelector('input[name="partner_involvement"]:checked');
        var invRate = inv ? (rates[inv.value] || 0) : 0;
        var rate = Math.max(catRate, invRate);
        var amt  = parseFloat(amtEl.value) || 0;
        if (amt > 0) {
            preview.classList.remove('d-none');
            amtOut.textContent = '€' + (amt * rate / 100).toFixed(2) + ' (' + rate.toFixed(1) + '%)';
        } else {
            preview.classList.add('d-none');
        }
    }

    document.querySelectorAll('input[name="partner_involvement"]').forEach(function(r) {
        r.addEventListener('change', update);
    });
    if (amtEl) amtEl.addEventListener('input', update);
}());
</script>
