<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>
<div class="row g-4 mt-1">
    <!-- Στοιχεία Επιχείρησης & Γρήγορες Ενέργειες -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-building me-1"></i><?= htmlspecialchars($business['company_name']) ?></div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><th class="text-muted fw-normal" width="35%">Επαφή</th><td><?= htmlspecialchars($business['contact_name']??'—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Email</th><td><a href="mailto:<?= htmlspecialchars($business['email']??'') ?>"><?= htmlspecialchars($business['email']??'—') ?></a></td></tr>
                    <tr><th class="text-muted fw-normal">Τηλέφωνο</th><td><?= htmlspecialchars($business['phone']??'—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Ιστοσελίδα</th><td><?= $business['website'] ? '<a href="'.htmlspecialchars($business['website']).'" target="_blank">Επίσκεψη</a>' : '—' ?></td></tr>
                    <tr><th class="text-muted fw-normal">Πόλη</th><td><?= htmlspecialchars($business['city']??'—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Κατηγορία</th><td><?= htmlspecialchars($business['category']??'—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Κατάσταση</th><td><span class="badge bg-primary"><?= grStatus($business['status']) ?></span></td></tr>
                </table>
            </div>
        </div>

        <!-- Γρήγορη Υποβολή Συμφωνίας -->
        <a href="<?= APP_URL ?>/caller/deals/create/<?= $business['id'] ?>" class="btn btn-success w-100 mb-3"><i class="bi bi-bag-plus me-1"></i>Υποβολή Συμφωνίας</a>

        <!-- Φόρμα Καταγραφής Αλληλεπίδρασης -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-plus-circle me-1"></i>Καταγραφή Αλληλεπίδρασης</div>
            <div class="card-body">
                <form id="interactionForm" method="POST" action="<?= APP_URL ?>/caller/interactions" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="business_id" value="<?= $business['id'] ?>">

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Τύπος</label>
                        <select name="type" id="intType" class="form-select form-select-sm">
                            <?php foreach(['call'=>'Τηλεφωνική Κλήση','email'=>'Email','offer'=>'Αποστολή Προσφοράς','demo'=>'Demo/Δοκιμή','follow_up'=>'Follow-up','messenger'=>'Messenger','whatsapp'=>'WhatsApp','reminder'=>'Υπενθύμιση'] as $v=>$l): ?>
                            <option value="<?= $v ?>"><?= $l ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="mb-2" id="resultRow">
                        <label class="form-label small fw-semibold">Αποτέλεσμα</label>
                        <select name="result" class="form-select form-select-sm">
                            <option value="">— Επιλογή —</option>
                            <?php foreach(['no_answer'=>'Δεν Απάντησε','callback'=>'Ζήτησε Επανάκληση','interested'=>'Ενδιαφέρον','not_interested'=>'Δεν Ενδιαφέρεται','left_message'=>'Άφησα Μήνυμα','sent'=>'Στάλθηκε','completed'=>'Ολοκληρώθηκε'] as $v=>$l): ?>
                            <option value="<?= $v ?>"><?= $l ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Ενημέρωση Κατάστασης Επιχείρησης</label>
                        <select name="business_status" class="form-select form-select-sm">
                            <option value="">— Διατήρηση Τρέχουσας —</option>
                            <?php foreach(['new','contacted','interested','not_interested','deal_closed','follow_up'] as $s): ?>
                            <option value="<?= $s ?>"><?= grStatus($s) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Προτεινόμενες Υπηρεσίες</label>
                        <div class="row g-1">
                            <?php foreach ($services as $svc): ?>
                            <div class="col-6">
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input" type="checkbox" name="services[]" value="<?= $svc['id'] ?>" id="svc_<?= $svc['id'] ?>">
                                    <label class="form-check-label small" for="svc_<?= $svc['id'] ?>"><?= htmlspecialchars($svc['name']) ?></label>
                                </div>
                            </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Σημειώσεις</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>

                    <div class="mb-2" id="durationRow">
                        <label class="form-label small fw-semibold">Διάρκεια (λεπτά)</label>
                        <input type="number" name="duration_min" class="form-control form-control-sm" min="1" max="999">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Προγραμματισμένο Follow-up</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ανέβασμα Πρότασης (PDF/DOC)</label>
                        <input type="file" name="proposal_file" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="bi bi-save me-1"></i>Καταγραφή Αλληλεπίδρασης</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Χρονολόγιο -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-clock-history me-1"></i>Ιστορικό Αλληλεπιδράσεων</div>
            <div id="timelineContainer" class="card-body p-0">
                <?php if(empty($interactions)): ?>
                <div class="text-center text-muted py-5">Δεν έχουν καταγραφεί αλληλεπιδράσεις. Χρησιμοποιήστε τη φόρμα για να καταγράψετε την πρώτη.</div>
                <?php else: ?>
                <div class="timeline px-4 py-3">
                    <?php foreach ($interactions as $int): ?>
                    <div class="timeline-item">
                        <div class="timeline-icon <?= intTypeColor2($int['type']) ?>"><i class="bi bi-<?= intTypeIcon2($int['type']) ?>"></i></div>
                        <div class="timeline-body ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= grIntType($int['type']) ?></strong>
                                    <span class="text-muted small ms-2"><?= date('d M Y H:i', strtotime($int['created_at'])) ?></span>
                                    <?php if($int['result']): ?><span class="badge bg-light text-dark border ms-1"><?= grIntResult($int['result']) ?></span><?php endif ?>
                                    <?php if($int['duration_min']): ?><span class="badge bg-light text-dark border ms-1"><?= $int['duration_min'] ?>λεπ.</span><?php endif ?>
                                </div>
                                <form method="POST" action="<?= APP_URL ?>/caller/interactions/<?= $int['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Διαγραφή αυτής της αλληλεπίδρασης;')">
                                    <?= CSRF::field() ?>
                                    <button class="btn btn-xs btn-outline-danger" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                            <?php if($int['notes']): ?><p class="mb-1 mt-1 small text-muted"><?= nl2br(htmlspecialchars($int['notes'])) ?></p><?php endif ?>
                            <?php if(!empty($int['services'])): ?>
                            <div class="mt-1"><?php foreach($int['services'] as $s): ?><span class="badge bg-secondary me-1 small"><?= htmlspecialchars($s['name']) ?></span><?php endforeach ?></div>
                            <?php endif ?>
                            <?php if($int['proposal_file']): ?>
                            <a href="<?= APP_URL ?>/assets/uploads/proposals/<?= htmlspecialchars($int['proposal_file']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary mt-1"><i class="bi bi-paperclip"></i> Πρόταση</a>
                            <?php endif ?>
                            <?php if($int['scheduled_at']): ?>
                            <div class="mt-1 small text-warning"><i class="bi bi-alarm me-1"></i>Υπενθύμιση: <?= date('d M Y H:i', strtotime($int['scheduled_at'])) ?></div>
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
function intTypeIcon2(string $t): string {
    return match($t) { 'call'=>'telephone','email'=>'envelope','offer'=>'file-earmark-text','demo'=>'camera-video','follow_up'=>'arrow-repeat','messenger'=>'chat-dots','whatsapp'=>'phone','reminder'=>'bell', default=>'circle' };
}
function intTypeColor2(string $t): string {
    return match($t) { 'call'=>'bg-primary','email'=>'bg-info','offer'=>'bg-success','demo'=>'bg-purple','follow_up'=>'bg-warning', default=>'bg-secondary' };
}
?>
<script>
document.getElementById('intType').addEventListener('change', function(){
    const isCall = this.value === 'call';
    document.getElementById('durationRow').style.display = isCall ? '' : 'none';
});
</script>
