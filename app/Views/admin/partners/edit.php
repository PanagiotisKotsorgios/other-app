<?php
use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
$colorMap = [
    'green'=>'#166534','blue'=>'#1d4ed8','orange'=>'#c2410c',
    'red'=>'#b91c1c','purple'=>'#7e22ce','teal'=>'#0f766e',
];
$bgMap = [
    'green'=>'#dcfce7','blue'=>'#dbeafe','orange'=>'#fff7ed',
    'red'=>'#fee2e2','purple'=>'#f3e8ff','teal'=>'#ccfbf1',
];
$currentCat = null;
foreach ($categories as $cat) {
    if ($cat['id'] == ($partner['category_id'] ?? null)) { $currentCat = $cat; break; }
}
$userId = $partner['id'];
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-handshake me-2"></i><?= htmlspecialchars($partner['name']) ?></h1>
    <a href="<?= APP_URL ?>/admin/partners" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω
    </a>
</div>

<div class="row g-4">
    <!-- Φόρμα Προφίλ -->
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-pencil me-1"></i>Προφίλ</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/partners/<?= $partner['id'] ?>/update">
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Ονοματεπώνυμο *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($partner['name']) ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($partner['email']) ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Τηλέφωνο</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($partner['phone'] ?? '') ?>">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Κατάσταση</label>
                            <select name="is_active" class="form-select">
                                <option value="1" <?= $partner['is_active'] ? 'selected' : '' ?>>Ενεργός</option>
                                <option value="0" <?= !$partner['is_active'] ? 'selected' : '' ?>>Ανενεργός</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Κατηγορία</label>
                            <select name="category_id" class="form-select">
                                <option value="">— Καμία —</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($partner['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                                    <?= grCategory($cat['name']) ?> — <?= htmlspecialchars($cat['label']) ?>
                                    (Παρ: <?= $cat['partner_rate'] ?>% | Ανάπτ: <?= $cat['developer_rate'] ?>%)
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Νέος Κωδικός <small class="text-muted">(αφήστε κενό για να διατηρήσετε)</small></label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Ρόλοι</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <?php foreach (['caller','developer','partner','admin'] as $r): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="roles[]" value="<?= $r ?>" id="role_<?= $r ?>"
                                           class="form-check-input" <?= in_array($r, $roles ?? []) ? 'checked' : '' ?>>
                                    <label for="role_<?= $r ?>" class="form-check-label"><?= grRole($r) ?></label>
                                </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3 justify-content-end">
                        <a href="<?= APP_URL ?>/admin/partners" class="btn btn-outline-secondary">Ακύρωση</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Αποθήκευση Αλλαγών</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Δεξιά: Κατηγορία + Σημειώσεις -->
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-award me-1"></i>Κατηγορία & Ποσοστό Προμήθειας</div>
            <div class="card-body">
                <?php if ($currentCat): ?>
                <?php $bg = $bgMap[$currentCat['color']] ?? '#dbeafe'; $txt = $colorMap[$currentCat['color']] ?? '#1d4ed8'; ?>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge fs-3 fw-800 px-3 py-2"
                          style="background:<?= $bg ?>;color:<?= $txt ?>;border:1px solid <?= $txt ?>30">
                        <?= htmlspecialchars($currentCat['name']) ?>
                    </span>
                    <div>
                        <div class="fw-600"><?= grCategory($currentCat['name']) ?> — <?= htmlspecialchars($currentCat['label']) ?></div>
                        <?php if($currentCat['description']): ?>
                        <div class="text-xs text-muted"><?= htmlspecialchars($currentCat['description']) ?></div>
                        <?php endif ?>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col">
                        <div class="stat-box text-center">
                            <div class="stat-val" style="color:<?= $txt ?>"><?= number_format($currentCat['partner_rate'], 1) ?>%</div>
                            <div class="stat-lbl">Παραπομπή</div>
                        </div>
                    </div>
                    <?php if(in_array('developer', $roles ?? [])): ?>
                    <div class="col">
                        <div class="stat-box text-center">
                            <div class="stat-val" style="color:#1d4ed8"><?= number_format($currentCat['developer_rate'], 1) ?>%</div>
                            <div class="stat-lbl">Ανάπτυξη</div>
                        </div>
                    </div>
                    <?php endif ?>
                    <?php if(in_array('caller', $roles ?? [])): ?>
                    <div class="col">
                        <div class="stat-box text-center">
                            <div class="stat-val" style="color:#0f766e"><?= number_format($currentCat['caller_rate'], 1) ?>%</div>
                            <div class="stat-lbl">Τηλεφωνητής</div>
                        </div>
                    </div>
                    <?php endif ?>
                </div>
                <div class="form-text mt-2 text-center">Τα ποσοστά αλλάζουν αλλάζοντας την Κατηγορία.</div>
                <?php else: ?>
                <div class="text-center text-muted py-3 text-sm">
                    <i class="bi bi-award fs-2 d-block mb-2 opacity-25"></i>
                    Χωρίς κατηγορία.<br>Χρήση καθολικού ποσοστού (<?= defined('PARTNER_COMMISSION_RATE') ? PARTNER_COMMISSION_RATE : 20 ?>%).
                </div>
                <?php endif ?>
            </div>
        </div>

        <?php include __DIR__ . '/../../_partials/user_notes.php' ?>
    </div>
</div>

<!-- Έγγραφα Συνεργάτη -->
<div class="card mt-4 border-0 shadow-sm">
    <div class="card-header fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-folder2-open me-1"></i>Έγγραφα Συνεργάτη
        <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#uploadPartnerDocModal">
            <i class="bi bi-upload me-1"></i>Ανέβασμα Εγγράφου
        </button>
    </div>
    <div class="card-body p-0">
        <?php
        $docTypeLabels = ['contract'=>'Σύμβαση','partner_invoice'=>'Τιμολόγιο Συνεργάτη','client_invoice'=>'Τιμολόγιο Πελάτη'];
        $docTypeColors = ['contract'=>'primary','partner_invoice'=>'success','client_invoice'=>'warning'];
        ?>
        <?php if (empty($partnerDocs)): ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
            Δεν υπάρχουν έγγραφα ακόμα.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Τύπος</th><th>Τίτλος</th><th>Ποσό</th><th>Αρχείο</th><th>Σημειώσεις</th><th>Ημερομηνία</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($partnerDocs as $doc): ?>
                <tr>
                    <td>
                        <span class="badge bg-<?= $docTypeColors[$doc['doc_type']] ?? 'secondary' ?>">
                            <?= $docTypeLabels[$doc['doc_type']] ?? $doc['doc_type'] ?>
                        </span>
                    </td>
                    <td class="fw-semibold"><?= htmlspecialchars($doc['title'] ?: '—') ?></td>
                    <td><?= $doc['amount'] !== null ? '€'.number_format((float)$doc['amount'],2) : '—' ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($doc['original_name']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($doc['notes'] ?? '—') ?></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                    <td class="text-end">
                        <a href="<?= APP_URL ?>/partner/documents/<?= $doc['id'] ?>/download"
                           class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-download"></i>
                        </a>
                        <form method="POST" action="<?= APP_URL ?>/admin/partner-documents/<?= $doc['id'] ?>/delete"
                              class="d-inline" onsubmit="return confirm('Διαγραφή εγγράφου;')">
                            <?= CSRF::field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?php endif ?>
    </div>
</div>

<!-- Modal Ανεβάσματος Εγγράφου -->
<div class="modal fade" id="uploadPartnerDocModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/admin/partners/<?= $userId ?>/documents/upload" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Ανέβασμα Εγγράφου Συνεργάτη</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Τύπος Εγγράφου *</label>
                        <select name="doc_type" class="form-select" required>
                            <option value="contract">Σύμβαση Συνεργασίας</option>
                            <option value="client_invoice">Τιμολόγιο προς Πελάτη</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Τίτλος</label>
                        <input type="text" name="title" class="form-control" placeholder="π.χ. Σύμβαση 2026 ή Τιμολόγιο #001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ποσό (€)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Αρχείο *</label>
                        <input type="file" name="doc_file" class="form-control" accept=".pdf,.doc,.docx" required>
                        <div class="form-text">PDF, DOC, DOCX — έως 10MB</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Σημειώσεις</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Μεταφόρτωση</button>
                </div>
            </div>
        </form>
    </div>
</div>
