<?php use App\Core\{CSRF, Auth};
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>
<div class="row g-4 mt-1">
    <!-- Πληροφορίες Επιχείρησης -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-building me-1"></i> Στοιχεία Επιχείρησης</span>
                <a href="<?= APP_URL ?>/admin/businesses/<?= $business['id'] ?>/edit" class="btn btn-xs btn-outline-primary"><i class="bi bi-pencil"></i></a>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><th class="text-muted fw-normal" width="35%">Εταιρία</th><td class="fw-semibold"><?= htmlspecialchars($business['company_name']) ?></td></tr>
                    <tr><th class="text-muted fw-normal">Επαφή</th><td><?= htmlspecialchars($business['contact_name']??'—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Email</th><td><a href="mailto:<?= htmlspecialchars($business['email']??'') ?>"><?= htmlspecialchars($business['email']??'—') ?></a></td></tr>
                    <tr><th class="text-muted fw-normal">Τηλέφωνο</th><td><?= htmlspecialchars($business['phone']??'—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Website</th><td><?= $business['website'] ? '<a href="'.htmlspecialchars($business['website']).'" target="_blank">'.htmlspecialchars($business['website']).'</a>' : '—' ?></td></tr>
                    <tr><th class="text-muted fw-normal">Πόλη</th><td><?= htmlspecialchars($business['city']??'—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Κατηγορία</th><td><?= htmlspecialchars($business['category']??'—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Κατάσταση</th><td><span class="badge bg-primary"><?= grStatus($business['status']) ?></span></td></tr>
                </table>
                <?php if($business['notes']): ?><hr><p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($business['notes'])) ?></p><?php endif ?>
            </div>
        </div>
    </div>

    <!-- Ιστορικό Αλληλεπιδράσεων -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-clock-history me-1"></i> Ιστορικό Αλληλεπιδράσεων</div>
            <div class="card-body p-0">
                <?php if(empty($interactions)): ?>
                    <div class="text-center text-muted py-4">Δεν υπάρχουν αλληλεπιδράσεις ακόμα.</div>
                <?php else: ?>
                <div class="timeline px-4 py-3">
                <?php foreach ($interactions as $int): ?>
                    <div class="timeline-item">
                        <div class="timeline-icon <?= intTypeColor($int['type']) ?>">
                            <i class="bi bi-<?= intTypeIcon($int['type']) ?>"></i>
                        </div>
                        <div class="timeline-body ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= grIntType($int['type']) ?></strong>
                                    <span class="text-muted small ms-2"><?= date('d M Y H:i', strtotime($int['created_at'])) ?></span>
                                    <?php if($int['result']): ?><span class="badge bg-light text-dark ms-1"><?= grIntResult($int['result']) ?></span><?php endif ?>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted"><?= htmlspecialchars($int['caller_name']) ?></small>
                                    <form method="POST" action="<?= APP_URL ?>/caller/interactions/<?= $int['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Διαγραφή αυτής της αλληλεπίδρασης;')">
                                        <?= CSRF::field() ?>
                                        <button class="btn btn-xs btn-outline-danger" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <?php if($int['notes']): ?><p class="mb-1 mt-1 small"><?= nl2br(htmlspecialchars($int['notes'])) ?></p><?php endif ?>
                            <?php if(!empty($int['services'])): ?>
                                <div class="mt-1"><?php foreach($int['services'] as $s): ?><span class="badge bg-secondary me-1"><?= htmlspecialchars($s['name']) ?></span><?php endforeach ?></div>
                            <?php endif ?>
                            <?php if($int['proposal_file']): ?>
                                <a href="<?= APP_URL ?>/assets/uploads/proposals/<?= htmlspecialchars($int['proposal_file']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary mt-1"><i class="bi bi-paperclip"></i> Προσφορά</a>
                            <?php endif ?>
                        </div>
                    </div>
                <?php endforeach ?>
                </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<?php
function intTypeIcon(string $t): string {
    return match($t) { 'call'=>'telephone','email'=>'envelope','offer'=>'file-earmark-text','demo'=>'camera-video','follow_up'=>'arrow-repeat','messenger'=>'chat-dots','whatsapp'=>'whatsapp','reminder'=>'bell', default=>'circle' };
}
function intTypeColor(string $t): string {
    return match($t) { 'call'=>'bg-primary','email'=>'bg-info','offer'=>'bg-success','demo'=>'bg-purple','follow_up'=>'bg-warning', default=>'bg-secondary' };
}
?>
