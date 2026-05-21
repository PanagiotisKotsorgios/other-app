<?php
use App\Core\{CSRF, Session};
$old    = Session::getFlash('old', []);
$errors = Session::getFlash('errors', []);
$error  = Session::getFlash('error');
?>

<h2>Welcome back</h2>
<p class="auth-sub">Sign in to your account to continue.</p>

<?php if ($error): ?>
<div class="auth-alert">
    <i class="bi bi-exclamation-circle-fill"></i>
    <span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif ?>

<form method="POST" action="<?= APP_URL ?>/auth/login" id="loginForm" novalidate>
    <?= CSRF::field() ?>

    <!-- Email -->
    <div class="field-wrap">
        <label class="field-label" for="email">Email address</label>
        <div class="input-wrap">
            <input
                type="email"
                id="email"
                name="email"
                placeholder=" "
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                required
                autofocus
                autocomplete="email"
            >
            <i class="bi bi-envelope field-icon"></i>
        </div>
        <?php if (isset($errors['email'])): ?>
            <div class="invalid-msg"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($errors['email']) ?></div>
        <?php endif ?>
    </div>

    <!-- Password -->
    <div class="field-wrap">
        <label class="field-label" for="password">Password</label>
        <div class="input-wrap has-eye">
            <input
                type="password"
                id="password"
                name="password"
                placeholder=" "
                class="<?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                required
                autocomplete="current-password"
            >
            <i class="bi bi-lock field-icon"></i>
            <button type="button" class="eye-toggle" id="eyeBtn" aria-label="Toggle password visibility" tabindex="-1">
                <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
        </div>
        <?php if (isset($errors['password'])): ?>
            <div class="invalid-msg"><i class="bi bi-exclamation-circle"></i><?= htmlspecialchars($errors['password']) ?></div>
        <?php endif ?>
    </div>

    <button type="submit" class="btn-signin" id="submitBtn">
        <span class="spinner" id="spinner"></span>
        <i class="bi bi-box-arrow-in-right" id="submitIcon"></i>
        <span id="submitText">Sign In</span>
    </button>
</form>

<script>
(function () {
    // Eye toggle
    const pwInput  = document.getElementById('password');
    const eyeBtn   = document.getElementById('eyeBtn');
    const eyeIcon  = document.getElementById('eyeIcon');

    eyeBtn.addEventListener('click', function () {
        const show = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        eyeIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        pwInput.focus();
    });

    // Loading state on submit
    const form       = document.getElementById('loginForm');
    const spinner    = document.getElementById('spinner');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');
    const submitBtn  = document.getElementById('submitBtn');

    form.addEventListener('submit', function () {
        submitBtn.disabled  = true;
        spinner.style.display    = 'block';
        submitIcon.style.display = 'none';
        submitText.textContent   = 'Signing in…';
    });
})();
</script>
