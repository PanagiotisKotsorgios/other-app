<?php use App\Core\CSRF; ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pencil me-1"></i> Edit Caller: <?= htmlspecialchars($caller['name']) ?></div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/callers/<?= $caller['id'] ?>/update">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($caller['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($caller['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control" minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($caller['phone']??'') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?= $caller['is_active']?'selected':'' ?>>Active</option>
                            <option value="0" <?= !$caller['is_active']?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Update Caller</button>
                        <a href="<?= APP_URL ?>/admin/callers" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
