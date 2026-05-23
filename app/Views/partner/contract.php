<?php
use App\Core\CSRF;
require_once __DIR__ . '/../_partials/gr_helpers.php';
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-file-text me-2"></i>Σύμβαση Συνεργασίας</h1>
</div>

<?php if (empty($docs)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-file-earmark-x fs-1 text-muted d-block mb-3"></i>
        <p class="text-muted mb-1 fw-semibold">Δεν έχει ανεβεί σύμβαση ακόμα.</p>
        <p class="text-muted small">Επικοινωνήστε με τον διαχειριστή για να λάβετε τη σύμβαση συνεργασίας σας.</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-header fw-semibold"><i class="bi bi-file-earmark-check me-1"></i>Ενεργές Συμβάσεις</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Τίτλος</th>
                        <th>Αρχείο</th>
                        <th>Σημειώσεις</th>
                        <th>Ανεβάστηκε από</th>
                        <th>Ημερομηνία</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($docs as $doc): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($doc['title'] ?: 'Σύμβαση') ?></td>
                    <td>
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                        <span class="text-muted small"><?= htmlspecialchars($doc['original_name']) ?></span>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($doc['notes'] ?? '—') ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($doc['uploader_name']) ?></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/partner/documents/<?= $doc['id'] ?>/download"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Λήψη
                        </a>
                    </td>
                </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-3 d-flex gap-2 align-items-start">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong>Σημείωση:</strong> Για αναθεώρηση ή νέα σύμβαση, επικοινωνήστε με τον διαχειριστή σας.
    </div>
</div>
<?php endif ?>
