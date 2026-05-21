<?php
// Expects: $notes (array), $userId (int), $backUrl (string)
use App\Core\CSRF;
?>
<div class="card">
    <div class="card-header fw-semibold">
        <i class="bi bi-journal-text me-1"></i>Σημειώσεις Διαχειριστή
        <span class="badge bg-secondary ms-1"><?= count($notes) ?></span>
    </div>
    <div class="card-body p-0">

        <!-- Φόρμα προσθήκης σημείωσης -->
        <form method="POST" action="<?= APP_URL ?>/admin/users/<?= $userId ?>/notes" class="p-3 border-bottom">
            <?= CSRF::field() ?>
            <textarea name="body" class="form-control mb-2" rows="2"
                      placeholder="Προσθήκη σημείωσης για αυτόν τον χρήστη…" required></textarea>
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div class="form-check mb-0">
                    <input type="checkbox" name="is_pinned" value="1" id="pin_note" class="form-check-input">
                    <label for="pin_note" class="form-check-label text-sm">Καρφίτσωμα σημείωσης</label>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Προσθήκη Σημείωσης
                </button>
            </div>
        </form>

        <!-- Λίστα σημειώσεων -->
        <?php if (empty($notes)): ?>
        <div class="text-center text-muted py-4 text-sm">Δεν υπάρχουν σημειώσεις.</div>
        <?php else: ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($notes as $note): ?>
            <li class="list-group-item px-3 py-2 <?= $note['is_pinned'] ? 'bg-warning bg-opacity-10' : '' ?>">
                <div class="d-flex align-items-start gap-2">
                    <div class="flex-grow-1 min-w-0">
                        <?php if ($note['is_pinned']): ?>
                        <span class="badge bg-warning text-dark text-xs mb-1"><i class="bi bi-pin-fill me-1"></i>Καρφιτσωμένη</span>
                        <?php endif ?>
                        <p class="mb-1 text-sm"><?= nl2br(htmlspecialchars($note['body'])) ?></p>
                        <div class="text-xs text-muted">
                            <?= htmlspecialchars($note['author_name']) ?> &middot;
                            <?= date('d M Y H:i', strtotime($note['created_at'])) ?>
                        </div>
                    </div>
                    <form method="POST" action="<?= APP_URL ?>/admin/users/notes/<?= $note['id'] ?>/delete"
                          onsubmit="return confirm('Διαγραφή αυτής της σημείωσης;')" class="flex-shrink-0">
                        <?= CSRF::field() ?>
                        <button class="btn btn-xs btn-outline-danger" title="Διαγραφή"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </li>
            <?php endforeach ?>
        </ul>
        <?php endif ?>
    </div>
</div>
