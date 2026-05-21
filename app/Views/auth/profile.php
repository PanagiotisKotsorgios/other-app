<?php use App\Core\CSRF; ?>
<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person me-1"></i> Στοιχεία Προφίλ</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/auth/profile">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Ονοματεπώνυμο</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Τηλέφωνο</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <button class="btn btn-primary">Αποθήκευση Αλλαγών</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-lock me-1"></i> Αλλαγή Κωδικού</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/auth/password">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Τρέχων Κωδικός</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Νέος Κωδικός</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Επιβεβαίωση Κωδικού</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button class="btn btn-warning">Αλλαγή Κωδικού</button>
                </form>
            </div>
        </div>
    </div>
</div>
