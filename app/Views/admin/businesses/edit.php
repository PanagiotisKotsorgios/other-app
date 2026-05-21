<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pencil me-1"></i> Επεξεργασία Επιχείρησης: <?= htmlspecialchars($business['company_name']) ?></div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/businesses/<?= $business['id'] ?>/update">
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Επωνυμία Εταιρίας <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($business['company_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Υπεύθυνος Επαφής</label>
                            <input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($business['contact_name']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($business['email']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τηλέφωνο</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($business['phone']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($business['website']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Κατηγορία</label>
                            <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($business['category']??'') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Πόλη</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($business['city']??'') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Χώρα</label>
                            <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($business['country']??'') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Κατάσταση</label>
                            <select name="status" class="form-select">
                                <?php foreach (['new','contacted','interested','not_interested','deal_closed','follow_up'] as $s): ?>
                                <option value="<?= $s ?>" <?= $business['status']===$s?'selected':'' ?>><?= grStatus($s) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Διεύθυνση</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($business['address']??'') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Σημειώσεις</label>
                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($business['notes']??'') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Επαναφορά σε Τηλεφωνητή</label>
                            <select name="caller_id" class="form-select">
                                <option value="">— Διατήρηση τρέχοντος / Αφαίρεση —</option>
                                <?php foreach ($callers as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">Ενημέρωση Επιχείρησης</button>
                        <a href="<?= APP_URL ?>/admin/businesses" class="btn btn-outline-secondary">Ακύρωση</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
