<?php use App\Core\CSRF;
require_once __DIR__ . '/gr_helpers.php';
?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pencil-square me-1"></i>Νέο Μήνυμα</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/<?= $prefix ?>/messages/send">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Προς</label>
                        <select name="receiver_id" class="form-select" required>
                            <option value="">— Επιλογή Παραλήπτη —</option>
                            <?php foreach ($recipients as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($r['id']==$toId)?'selected':'' ?>>
                                <?= htmlspecialchars($r['name']) ?> (<?= grRole($r['role']) ?>)
                            </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Θέμα</label>
                        <input type="text" name="subject" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Μήνυμα</label>
                        <textarea name="body" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Αποστολή Μηνύματος</button>
                        <a href="<?= APP_URL ?>/<?= $prefix ?>/messages" class="btn btn-outline-secondary">Ακύρωση</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
