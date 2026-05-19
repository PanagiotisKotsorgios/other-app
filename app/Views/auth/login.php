<?php
use App\Core\{CSRF, Session};
$old    = Session::getFlash('old', []);
$errors = Session::getFlash('errors', []);
$error  = Session::getFlash('error');
?>
<?php if ($error): ?>
    <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif ?>

<form method="POST" action="<?= APP_URL ?>/auth/login">
    <?= CSRF::field() ?>

    <div class="mb-3">
        <label class="form-label fw-semibold">Email address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control <?= isset($errors['email'])?'is-invalid':'' ?>"
                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required autofocus>
            <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif ?>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label fw-semibold">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control <?= isset($errors['password'])?'is-invalid':'' ?>" required>
            <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div><?php endif ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
    </button>
</form>
