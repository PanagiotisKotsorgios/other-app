<?php use App\Core\CSRF; ?>
<div class="row justify-content-center mt-2">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-handshake me-1"></i> Προσθήκη Συνεργάτη</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/partners">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Ονοματεπώνυμο *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Τηλέφωνο</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Κωδικός *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?= APP_URL ?>/admin/partners" class="btn btn-outline-secondary">Ακύρωση</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Δημιουργία Συνεργάτη</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
