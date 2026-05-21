<?php
use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
$colorMap = [
    'green'=>'#166534','blue'=>'#1d4ed8','orange'=>'#c2410c',
    'red'=>'#b91c1c','purple'=>'#7e22ce','teal'=>'#0f766e',
];
$bgMap = [
    'green'=>'#dcfce7','blue'=>'#dbeafe','orange'=>'#fff7ed',
    'red'=>'#fee2e2','purple'=>'#f3e8ff','teal'=>'#ccfbf1',
];
$currentCat = null;
foreach ($categories as $cat) {
    if ($cat['id'] == ($caller['category_id'] ?? null)) { $currentCat = $cat; break; }
}
$userId = $caller['id'];
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-person-badge me-2"></i><?= htmlspecialchars($caller['name']) ?></h1>
    <div class="page-header-actions">
        <a href="<?= APP_URL ?>/admin/callers/<?= $caller['id'] ?>/stats" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-bar-chart me-1"></i>Στατιστικά
        </a>
        <a href="<?= APP_URL ?>/admin/callers" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Πίσω
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Φόρμα Προφίλ -->
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-pencil me-1"></i>Προφίλ</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/callers/<?= $caller['id'] ?>/update">
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Ονοματεπώνυμο *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($caller['name']) ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($caller['email']) ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Τηλέφωνο</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($caller['phone'] ?? '') ?>">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Κατάσταση</label>
                            <select name="is_active" class="form-select">
                                <option value="1" <?= $caller['is_active'] ? 'selected' : '' ?>>Ενεργός</option>
                                <option value="0" <?= !$caller['is_active'] ? 'selected' : '' ?>>Ανενεργός</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label">Κατηγορία</label>
                            <select name="category_id" class="form-select" id="callerCatSelect">
                                <option value="">— Καμία —</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($caller['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?> — <?= htmlspecialchars($cat['label']) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Νέος Κωδικός <small class="text-muted">(αφήστε κενό για να διατηρήσετε)</small></label>
                            <input type="password" name="password" class="form-control" minlength="8">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3 justify-content-end">
                        <a href="<?= APP_URL ?>/admin/callers" class="btn btn-outline-secondary">Ακύρωση</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Αποθήκευση Αλλαγών</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Δεξιά: Κατηγορία + Σημειώσεις -->
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-award me-1"></i>Κατηγορία & Ποσοστό Προμήθειας</div>
            <div class="card-body">
                <?php if ($currentCat): ?>
                <?php $bg = $bgMap[$currentCat['color']] ?? '#dbeafe'; $txt = $colorMap[$currentCat['color']] ?? '#1d4ed8'; ?>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge fs-3 fw-800 px-3 py-2"
                          style="background:<?= $bg ?>;color:<?= $txt ?>;border:1px solid <?= $txt ?>30">
                        <?= htmlspecialchars($currentCat['name']) ?>
                    </span>
                    <div>
                        <div class="fw-600"><?= htmlspecialchars($currentCat['label']) ?></div>
                        <?php if($currentCat['description']): ?>
                        <div class="text-xs text-muted"><?= htmlspecialchars($currentCat['description']) ?></div>
                        <?php endif ?>
                    </div>
                </div>
                <div class="stat-box text-center">
                    <div class="stat-val"><?= number_format($currentCat['caller_rate'], 1) ?>%</div>
                    <div class="stat-lbl">Ποσοστό Προμήθειας Τηλεφωνητή</div>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-3 text-sm">
                    <i class="bi bi-award fs-2 d-block mb-2 opacity-25"></i>
                    Χωρίς κατηγορία.<br>Χρήση καθολικού ποσοστού (<?= COMMISSION_RATE ?>%).
                </div>
                <?php endif ?>
            </div>
        </div>

        <?php include __DIR__ . '/../../_partials/user_notes.php' ?>
    </div>
</div>
