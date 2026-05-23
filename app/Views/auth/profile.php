<?php use App\Core\CSRF; ?>
<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person me-1"></i>Στοιχεία Προφίλ</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/auth/profile">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Ονοματεπώνυμο</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Τηλέφωνο</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Αποθήκευση Αλλαγών</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-lock me-1"></i>Αλλαγή Κωδικού</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/auth/password">
                    <?= CSRF::field() ?>

                    <div class="mb-3">
                        <label class="form-label">Τρέχων Κωδικός</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="pw_current" class="form-control" required autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('pw_current', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Νέος Κωδικός</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="pw_new" class="form-control" required minlength="8" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('pw_new', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Τουλάχιστον 8 χαρακτήρες.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Επιβεβαίωση Νέου Κωδικού</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="pw_confirm" class="form-control" required autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('pw_confirm', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button class="btn btn-warning"><i class="bi bi-shield-lock me-1"></i>Αλλαγή Κωδικού</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    var input = document.getElementById(id);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
