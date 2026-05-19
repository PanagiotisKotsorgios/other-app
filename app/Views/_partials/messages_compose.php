<?php use App\Core\CSRF; ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pencil-square me-1"></i>New Message</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/<?= $prefix ?>/messages/send">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">To</label>
                        <select name="receiver_id" class="form-select" required>
                            <option value="">— Select Recipient —</option>
                            <?php foreach ($recipients as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= ($r['id']==$toId)?'selected':'' ?>>
                                <?= htmlspecialchars($r['name']) ?> (<?= ucfirst($r['role']) ?>)
                            </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="body" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Send Message</button>
                        <a href="<?= APP_URL ?>/<?= $prefix ?>/messages" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
