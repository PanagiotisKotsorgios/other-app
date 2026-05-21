<?php use App\Core\CSRF; ?>

<div class="page-header">
    <h1 class="page-title">Επεξεργασία Κατηγορίας</h1>
    <a href="<?= APP_URL ?>/admin/categories" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-6">

<form method="POST" action="<?= APP_URL ?>/admin/categories/<?= $category['id'] ?>/update">
    <?= CSRF::field() ?>

    <?php if (!empty($errors)): ?>
    <div class="flash-alert flash-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <ul class="mb-0 ps-3"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach ?></ul>
    </div>
    <?php endif ?>

    <div class="card">
        <div class="card-header fw-semibold"><i class="bi bi-award me-1"></i>Στοιχεία Κατηγορίας</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-4">
                    <label class="form-label">Όνομα *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>"
                           maxlength="20" required>
                </div>
                <div class="col-sm-5">
                    <label class="form-label">Ετικέτα</label>
                    <input type="text" name="label" class="form-control" value="<?= htmlspecialchars($category['label']) ?>"
                           maxlength="100">
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Σειρά Ταξινόμησης</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int)$category['sort_order'] ?>" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Χρώμα</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php
                        $colors = [
                            'green'  => ['bg'=>'#dcfce7','text'=>'#166534','label'=>'Πράσινο'],
                            'blue'   => ['bg'=>'#dbeafe','text'=>'#1d4ed8','label'=>'Μπλε'],
                            'orange' => ['bg'=>'#fff7ed','text'=>'#c2410c','label'=>'Πορτοκαλί'],
                            'red'    => ['bg'=>'#fee2e2','text'=>'#b91c1c','label'=>'Κόκκινο'],
                            'purple' => ['bg'=>'#f3e8ff','text'=>'#7e22ce','label'=>'Μωβ'],
                            'teal'   => ['bg'=>'#ccfbf1','text'=>'#0f766e','label'=>'Τιρκουάζ'],
                        ];
                        foreach ($colors as $key => $c):
                        ?>
                        <label class="d-flex align-items-center gap-1" style="cursor:pointer">
                            <input type="radio" name="color" value="<?= $key ?>"
                                   <?= $category['color'] === $key ? 'checked' : '' ?> class="form-check-input m-0">
                            <span class="badge" style="background:<?= $c['bg'] ?>;color:<?= $c['text'] ?>;border:1px solid <?= $c['text'] ?>20;font-size:.8rem">
                                <?= $c['label'] ?>
                            </span>
                        </label>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Περιγραφή</label>
                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header fw-semibold"><i class="bi bi-percent me-1"></i>Ποσοστά Προμήθειας</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-4">
                    <label class="form-label">Ποσοστό Τηλεφωνητή (%)</label>
                    <div class="input-group">
                        <input type="number" name="caller_rate" class="form-control"
                               value="<?= number_format($category['caller_rate'], 2) ?>"
                               step="0.1" min="0" max="100" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <label class="form-label">Ποσοστό Προγραμματιστή (%)</label>
                    <div class="input-group">
                        <input type="number" name="developer_rate" class="form-control"
                               value="<?= number_format($category['developer_rate'], 2) ?>"
                               step="0.1" min="0" max="100" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-sm-4">
                    <label class="form-label">Ποσοστό Συνεργάτη (%)</label>
                    <div class="input-group">
                        <input type="number" name="partner_rate" class="form-control"
                               value="<?= number_format($category['partner_rate'], 2) ?>"
                               step="0.1" min="0" max="100" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3 justify-content-end">
        <a href="<?= APP_URL ?>/admin/categories" class="btn btn-outline-secondary">Ακύρωση</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Αποθήκευση Αλλαγών</button>
    </div>
</form>

</div>
</div>
