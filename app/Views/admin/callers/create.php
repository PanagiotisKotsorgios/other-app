<?php use App\Core\{CSRF, Session}; $old = Session::getFlash('old',[]); $errors = Session::getFlash('errors',[]); ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-plus me-1"></i> Add Caller</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/callers">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= isset($errors['name'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($old['name']??'') ?>" required>
                        <?php if(isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control <?= isset($errors['email'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($old['email']??'') ?>" required>
                        <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($old['phone']??'') ?>">
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Create Caller</button>
                        <a href="<?= APP_URL ?>/admin/callers" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
