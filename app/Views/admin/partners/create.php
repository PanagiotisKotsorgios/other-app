<?php
use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>
<div class="row justify-content-center mt-2">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-handshake me-1"></i> Προσθήκη Συνεργάτη</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/partners">
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ονοματεπώνυμο *</label>
                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_SESSION['old']['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_SESSION['old']['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τηλέφωνο</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_SESSION['old']['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Κωδικός *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Κατηγορία</label>
                            <select name="category_id" class="form-select">
                                <option value="">— Καμία —</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= grCategory($cat['name']) ?> — <?= htmlspecialchars($cat['label']) ?>
                                    (Παρ: <?= $cat['partner_rate'] ?>% | Ανάπτ: <?= $cat['developer_rate'] ?>%)
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Επιπλέον Ρόλοι</label>
                            <div class="d-flex gap-3 flex-wrap mt-1">
                                <?php foreach (['developer','caller'] as $r): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="extra_roles[]" value="<?= $r ?>" id="role_<?= $r ?>" class="form-check-input">
                                    <label for="role_<?= $r ?>" class="form-check-label"><?= grRole($r) ?></label>
                                </div>
                                <?php endforeach ?>
                            </div>
                            <div class="form-text">Ο ρόλος Συνεργάτη προστίθεται αυτόματα. Ο Προγραμματιστής επιτρέπει ανάληψη έργων.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4 justify-content-end">
                        <a href="<?= APP_URL ?>/admin/partners" class="btn btn-outline-secondary">Ακύρωση</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Δημιουργία Συνεργάτη</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
