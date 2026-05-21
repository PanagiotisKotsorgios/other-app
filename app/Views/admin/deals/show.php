<?php
use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
$deal = $deal ?? [];
?>
<div class="row g-4 mt-1">
    <!-- Στοιχεία Συμφωνίας -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bag-check me-1"></i> Στοιχεία Συμφωνίας</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><th class="text-muted fw-normal" width="40%">Επιχείρηση</th><td class="fw-semibold"><?= htmlspecialchars($deal['company_name'] ?? '') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Τηλεφωνητής</th><td><?= htmlspecialchars($deal['caller_name'] ?? '') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Προγραμματιστής</th><td><?= htmlspecialchars($deal['developer_name'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Συνεργάτης</th><td>
                        <?= htmlspecialchars($deal['partner_name'] ?? '—') ?>
                        <?php if(!empty($deal['partner_involvement'])): ?>
                        <span class="badge bg-info text-dark ms-1" title="Επίπεδο συμμετοχής">
                            <?= grInvolvement($deal['partner_involvement']) ?>
                        </span>
                        <?php endif ?>
                    </td></tr>
                    <tr><th class="text-muted fw-normal">Υπηρεσία</th><td><?= htmlspecialchars($deal['service_name'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Ποσό</th><td class="fw-bold fs-5 text-success">€<?= number_format($deal['amount'] ?? 0, 2) ?></td></tr>
                    <tr><th class="text-muted fw-normal">Κατάσταση</th><td>
                        <span class="badge fs-6 <?= match($deal['status'] ?? ''){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>">
                            <?= grStatus($deal['status'] ?? '') ?>
                        </span>
                    </td></tr>
                    <tr><th class="text-muted fw-normal">Σύμβαση</th><td>
                        <?= ($deal['contract_signed'] ?? 0) ? '<span class="badge bg-success">Υπογεγραμμένη</span>' : '<span class="badge bg-warning text-dark">Μη Υπογεγραμμένη</span>' ?>
                    </td></tr>
                    <tr><th class="text-muted fw-normal">Δημιουργήθηκε</th><td><?= isset($deal['created_at']) ? date('d M Y H:i', strtotime($deal['created_at'])) : '—' ?></td></tr>
                    <?php if(!empty($deal['approved_at'])): ?>
                    <tr><th class="text-muted fw-normal">Εγκρίθηκε Από</th><td><?= htmlspecialchars($deal['approved_by_name'] ?? '') ?> — <?= date('d M Y', strtotime($deal['approved_at'])) ?></td></tr>
                    <?php endif ?>
                </table>
                <?php if(!empty($deal['notes'])): ?><hr><p class="small text-muted"><?= nl2br(htmlspecialchars($deal['notes'])) ?></p><?php endif ?>
                <?php if(!empty($deal['proposal_file'])): ?><a href="<?= APP_URL ?>/assets/uploads/proposals/<?= htmlspecialchars($deal['proposal_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip me-1"></i>Προβολή Προσφοράς</a><?php endif ?>
            </div>
        </div>

        <!-- Σύνοψη Προμήθειας -->
        <?php if(in_array($deal['status'] ?? '', ['approved','in_progress','completed'])): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-currency-euro me-1"></i> Σύνοψη Προμήθειας</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <?php
                    $catModel = new \App\Models\Category();
                    $callerRate = $catModel->rateForUser($deal['caller_id'] ?? 0, 'caller') ?: COMMISSION_RATE;
                    ?>
                    <tr><td>Τηλεφωνητής (<?= $callerRate ?>%)</td><td class="text-end fw-semibold">€<?= number_format($deal['amount'] * $callerRate / 100, 2) ?></td></tr>
                    <?php if(!empty($deal['developer_id'])): ?>
                    <?php $devRate = $catModel->rateForUser($deal['developer_id'], 'developer') ?: DEVELOPER_COMMISSION_RATE; ?>
                    <tr><td>Προγραμματιστής (<?= $devRate ?>%)</td><td class="text-end fw-semibold">€<?= number_format($deal['amount'] * $devRate / 100, 2) ?></td></tr>
                    <?php endif ?>
                    <?php if(!empty($deal['partner_id'])): ?>
                    <?php
                    $pCatRate = $catModel->rateForUser($deal['partner_id'], 'partner') ?: PARTNER_COMMISSION_RATE;
                    $invRates = ['contact'=>10.0,'presentation'=>13.0,'active_support'=>16.0,'full_closure'=>20.0];
                    $pInvRate = $invRates[$deal['partner_involvement'] ?? ''] ?? null;
                    $pRate    = $pInvRate !== null ? max($pCatRate, $pInvRate) : $pCatRate;
                    ?>
                    <tr><td>
                        Συνεργάτης (<?= $pRate ?>%)
                        <?php if(!empty($deal['partner_involvement'])): ?>
                        <span class="text-muted small">· <?= grInvolvement($deal['partner_involvement']) ?></span>
                        <?php endif ?>
                    </td><td class="text-end fw-semibold">€<?= number_format($deal['amount'] * $pRate / 100, 2) ?></td></tr>
                    <?php endif ?>
                </table>
            </div>
        </div>
        <?php endif ?>
    </div>

    <div class="col-lg-7">
        <!-- Ενέργειες -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-lightning me-1"></i> Ενέργειες</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php if(($deal['status']??'')==='pending'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/approve">
                        <?= CSRF::field() ?>
                        <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Έγκριση Συμφωνίας</button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/reject">
                        <?= CSRF::field() ?>
                        <button class="btn btn-danger"><i class="bi bi-x-circle me-1"></i>Απόρριψη Συμφωνίας</button>
                    </form>
                    <?php endif ?>
                    <?php if(($deal['status']??'')==='approved'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/in-progress">
                        <?= CSRF::field() ?>
                        <button class="btn btn-primary"><i class="bi bi-arrow-right-circle me-1"></i>Σε Εξέλιξη</button>
                    </form>
                    <?php endif ?>
                    <?php if(($deal['status']??'')==='in_progress'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/completed">
                        <?= CSRF::field() ?>
                        <button class="btn btn-info text-white"><i class="bi bi-check2-all me-1"></i>Ολοκλήρωση</button>
                    </form>
                    <?php endif ?>

                    <?php if(!($deal['contract_signed'] ?? 0) && in_array($deal['status']??'',['approved','in_progress'])): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/sign-contract">
                        <?= CSRF::field() ?>
                        <button class="btn btn-outline-success"><i class="bi bi-pen me-1"></i>Υπογραφή Σύμβασης & Δημιουργία Έργου</button>
                    </form>
                    <?php elseif($deal['contract_signed'] ?? 0): ?>
                    <a href="<?= APP_URL ?>/admin/projects" class="btn btn-outline-primary"><i class="bi bi-kanban me-1"></i>Προβολή Έργου</a>
                    <?php endif ?>

                    <a href="<?= APP_URL ?>/admin/deals" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Πίσω</a>
                </div>
            </div>
        </div>

        <!-- Ανάθεση Προγραμματιστή & Συνεργάτη -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-badge me-1"></i> Ανάθεση Προγραμματιστή & Συνεργάτη</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/assign-developer">
                            <?= CSRF::field() ?>
                            <label class="form-label small fw-semibold">Προγραμματιστής</label>
                            <?php
                            $devList = (new \App\Models\User())->developers();
                            ?>
                            <select name="developer_id" class="form-select form-select-sm mb-2">
                                <option value="">— Χωρίς Ανάθεση —</option>
                                <?php foreach ($devList as $dev): ?>
                                <option value="<?= $dev['id'] ?>" <?= ($deal['developer_id']??0)==$dev['id']?'selected':'' ?>><?= htmlspecialchars($dev['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                            <button class="btn btn-sm btn-outline-primary w-100">Ανάθεση Προγραμματιστή</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/assign-partner">
                            <?= CSRF::field() ?>
                            <label class="form-label small fw-semibold">Συνεργάτης</label>
                            <?php
                            $partnerList = (new \App\Models\User())->partners();
                            ?>
                            <select name="partner_id" class="form-select form-select-sm mb-2">
                                <option value="">— Χωρίς Ανάθεση —</option>
                                <?php foreach ($partnerList as $pt): ?>
                                <option value="<?= $pt['id'] ?>" <?= ($deal['partner_id']??0)==$pt['id']?'selected':'' ?>><?= htmlspecialchars($pt['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                            <button class="btn btn-sm btn-outline-warning w-100">Ανάθεση Συνεργάτη</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ανέβασμα Σύμβασης -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-file-earmark-pdf me-1"></i> Έγγραφα Σύμβασης</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/documents/contracts/<?= $deal['id'] ?>/upload" enctype="multipart/form-data" class="mb-3">
                    <?= CSRF::field() ?>
                    <div class="input-group input-group-sm">
                        <input type="file" name="contract_file" class="form-control" accept=".pdf,.doc,.docx" required>
                        <button class="btn btn-outline-primary"><i class="bi bi-upload me-1"></i>Ανέβασμα</button>
                    </div>
                    <input type="text" name="notes" class="form-control form-control-sm mt-2" placeholder="Σημειώσεις (προαιρετικό)">
                </form>
                <?php
                $contracts = (new \App\Models\Contract())->forDeal($deal['id']);
                ?>
                <?php if(empty($contracts)): ?>
                <p class="text-muted small mb-0">Δεν έχουν αναρτηθεί συμβάσεις.</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($contracts as $c): ?>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                            <span class="small"><?= htmlspecialchars($c['original_name']) ?></span>
                            <div class="text-muted" style="font-size:.7rem"><?= date('d M Y', strtotime($c['uploaded_at'])) ?> · <?= htmlspecialchars($c['uploader_name']) ?></div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="<?= APP_URL ?>/admin/documents/contracts/<?= $c['id'] ?>/download" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                            <form method="POST" action="<?= APP_URL ?>/admin/documents/contracts/<?= $c['id'] ?>/delete" onsubmit="return confirm('Διαγραφή;')">
                                <?= CSRF::field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
        </div>

        <!-- Τιμολόγια -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-1"></i> Τιμολόγια</span>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                    <i class="bi bi-plus-lg me-1"></i>Προσθήκη Τιμολογίου
                </button>
            </div>
            <div class="card-body p-0">
                <?php
                $invoices = (new \App\Models\Invoice())->forDeal($deal['id']);
                ?>
                <?php if(empty($invoices)): ?>
                <div class="text-center text-muted py-3 small">Δεν υπάρχουν τιμολόγια ακόμα.</div>
                <?php else: ?>
                <table class="table table-hover mb-0 table-sm">
                    <thead class="table-light"><tr><th>Αρ.</th><th>Ποσό</th><th>ΦΠΑ</th><th>Σύνολο</th><th>Κατάσταση</th><th>Λήξη</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><?= htmlspecialchars($inv['invoice_no'] ?? '—') ?></td>
                            <td>€<?= number_format($inv['amount'],2) ?></td>
                            <td>€<?= number_format($inv['vat_amount'],2) ?></td>
                            <td class="fw-semibold">€<?= number_format($inv['total_amount'],2) ?></td>
                            <td><span class="badge <?= match($inv['status']){'draft'=>'bg-secondary','issued'=>'bg-info text-dark','sent'=>'bg-primary','paid'=>'bg-success',default=>'bg-secondary'} ?>"><?= grStatus($inv['status']) ?></span></td>
                            <td><?= $inv['due_at'] ? date('d M Y', strtotime($inv['due_at'])) : '—' ?></td>
                            <td>
                                <?php if($inv['status']!=='paid'): ?>
                                <form method="POST" action="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/mark-paid" class="d-inline">
                                    <?= CSRF::field() ?><button class="btn btn-xs btn-outline-success" style="font-size:.7rem;padding:2px 6px">Πληρωμένο</button>
                                </form>
                                <?php endif ?>
                                <?php if($inv['filename']): ?>
                                <a href="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/download" class="btn btn-outline-secondary" style="font-size:.7rem;padding:2px 6px"><i class="bi bi-download"></i></a>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- Μοντάλ Προσθήκης Τιμολογίου -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/admin/documents/invoices/<?= $deal['id'] ?>/upload" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Προσθήκη Τιμολογίου</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Αρ. Τιμολογίου</label><input type="text" name="invoice_no" class="form-control" placeholder="ΤΔΑ-001"></div>
                        <div class="col-6"><label class="form-label">Ποσό (€) *</label><input type="number" name="amount" class="form-control" step="0.01" required></div>
                        <div class="col-6"><label class="form-label">ΦΠΑ (%)</label><input type="number" name="vat_rate" class="form-control" value="24" step="0.01"></div>
                        <div class="col-6"><label class="form-label">Κατάσταση</label>
                            <select name="status" class="form-select"><option value="draft">Πρόχειρο</option><option value="issued">Εκδόθηκε</option><option value="sent">Στάλθηκε</option></select>
                        </div>
                        <div class="col-6"><label class="form-label">Ημερομηνία Έκδοσης</label><input type="date" name="issued_at" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Ημερομηνία Λήξης</label><input type="date" name="due_at" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Αρχείο PDF (προαιρετικό)</label><input type="file" name="invoice_file" class="form-control" accept=".pdf"></div>
                        <div class="col-12"><label class="form-label">Σημειώσεις</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Δημιουργία Τιμολογίου</button></div>
            </div>
        </form>
    </div>
</div>
