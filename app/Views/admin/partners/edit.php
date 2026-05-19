<!-- E:\call_center\app\Views\admin\partners\edit.php -->
<?php use App\Core\CSRF; ?>
<div class="row justify-content-center mt-2">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pencil me-1"></i> Edit Partner: <?= htmlspecialchars($partner['name']) ?></div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/partners/<?= $partner['id'] ?>/update">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($partner['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($partner['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($partner['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?= $partner['is_active']?'selected':'' ?>>Active</option>
                            <option value="0" <?= !$partner['is_active']?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Roles</label>
                        <div class="d-flex gap-3">
                            <?php foreach (['caller','developer','partner','admin'] as $r): ?>
                            <div class="form-check">
                                <input type="checkbox" name="roles[]" value="<?= $r ?>" id="role_<?= $r ?>" class="form-check-input"
                                    <?= in_array($r, $roles ?? []) ? 'checked' : '' ?>>
                                <label for="role_<?= $r ?>" class="form-check-label"><?= ucfirst($r) ?></label>
                            </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?= APP_URL ?>/admin/partners" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
