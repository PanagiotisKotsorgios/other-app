<?php
use App\Core\CSRF;
require_once __DIR__ . '/../_partials/gr_helpers.php';
?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h1 class="page-title"><i class="bi bi-receipt me-2"></i>Τιμολόγια</h1>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadInvoiceModal">
        <i class="bi bi-upload me-1"></i>Ανέβασμα Τιμολογίου
    </button>
</div>

<!-- Τα τιμολόγιά μου (partner → εταιρεία) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-arrow-up-circle text-primary"></i>
        Τιμολόγιά μου προς SoftSystems
        <span class="badge bg-primary ms-auto"><?= count($myInvoices) ?></span>
    </div>
    <?php if (empty($myInvoices)): ?>
    <div class="card-body text-center py-4 text-muted">
        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
        Δεν έχετε ανεβάσει τιμολόγια ακόμα.
    </div>
    <?php else: ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Τίτλος</th><th>Ποσό</th><th>Αρχείο</th><th>Σημειώσεις</th><th>Ημερομηνία</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($myInvoices as $doc): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($doc['title'] ?: 'Τιμολόγιο') ?></td>
                    <td><?= $doc['amount'] !== null ? '€'.number_format((float)$doc['amount'],2) : '—' ?></td>
                    <td class="text-muted small">
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                        <?= htmlspecialchars($doc['original_name']) ?>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($doc['notes'] ?? '—') ?></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                    <td class="text-end">
                        <a href="<?= APP_URL ?>/partner/documents/<?= $doc['id'] ?>/download"
                           class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-download"></i>
                        </a>
                        <form method="POST" action="<?= APP_URL ?>/partner/documents/<?= $doc['id'] ?>/delete"
                              class="d-inline" onsubmit="return confirm('Διαγραφή τιμολογίου;')">
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
    </div>
    <?php endif ?>
</div>

<!-- Τιμολόγια εταιρείας προς πελάτες -->
<div class="card border-0 shadow-sm">
    <div class="card-header fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-arrow-down-circle text-success"></i>
        Τιμολόγια SoftSystems προς Πελάτες
        <span class="badge bg-success ms-auto"><?= count($clientInvoices) ?></span>
    </div>
    <?php if (empty($clientInvoices)): ?>
    <div class="card-body text-center py-4 text-muted">
        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
        Δεν υπάρχουν τιμολόγια προς πελάτες ακόμα.
    </div>
    <?php else: ?>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Τίτλος</th><th>Ποσό</th><th>Αρχείο</th><th>Σημειώσεις</th><th>Ημερομηνία</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($clientInvoices as $doc): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($doc['title'] ?: 'Τιμολόγιο Πελάτη') ?></td>
                    <td><?= $doc['amount'] !== null ? '€'.number_format((float)$doc['amount'],2) : '—' ?></td>
                    <td class="text-muted small">
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                        <?= htmlspecialchars($doc['original_name']) ?>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($doc['notes'] ?? '—') ?></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/partner/documents/<?= $doc['id'] ?>/download"
                           class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i>Λήψη
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif ?>
</div>

<!-- Modal Ανεβάσματος -->
<div class="modal fade" id="uploadInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/partner/invoices/upload" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Ανέβασμα Τιμολογίου</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Τίτλος Τιμολογίου</label>
                        <input type="text" name="title" class="form-control" placeholder="π.χ. Τιμολόγιο #001 - Μάιος 2026">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ποσό (€)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Αρχείο Τιμολογίου *</label>
                        <input type="file" name="invoice_file" class="form-control" accept=".pdf,.doc,.docx" required>
                        <div class="form-text">Επιτρεπόμενοι τύποι: PDF, DOC, DOCX (έως 10MB)</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Σημειώσεις</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Προαιρετικές σημειώσεις..."></textarea>
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
