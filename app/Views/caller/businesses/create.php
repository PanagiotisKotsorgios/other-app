<?php use App\Core\{CSRF, Session}; $old = Session::getFlash('old', []); $errors = Session::getFlash('errors', []); ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Νέο Lead</h4>
        <p class="text-muted small mb-0">Καταχωρήστε μια νέα επιχείρηση που θέλετε να επικοινωνήσετε</p>
    </div>
    <a href="<?= APP_URL ?>/caller/businesses" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Πίσω
    </a>
</div>

<?php if($err = Session::getFlash('error')): ?>
<div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
<?php endif ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-building-add me-1 text-primary"></i>Στοιχεία Επιχείρησης
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/caller/businesses/store">
                    <?= CSRF::field() ?>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Επωνυμία Εταιρίας <span class="text-danger">*</span></label>
                            <input type="text" name="company_name"
                                   class="form-control <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['company_name'] ?? '') ?>"
                                   placeholder="π.χ. ABC Ε.Π.Ε." required>
                            <?php if(isset($errors['company_name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['company_name']) ?></div>
                            <?php endif ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Υπεύθυνος Επαφής</label>
                            <input type="text" name="contact_name" class="form-control"
                                   value="<?= htmlspecialchars($old['contact_name'] ?? '') ?>"
                                   placeholder="Ονοματεπώνυμο">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Τηλέφωνο</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                                   placeholder="π.χ. 2310123456">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                                   placeholder="info@example.gr">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Website</label>
                            <input type="url" name="website" class="form-control"
                                   value="<?= htmlspecialchars($old['website'] ?? '') ?>"
                                   placeholder="https://www.example.gr">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Κατηγορία</label>
                            <input type="text" name="category" class="form-control"
                                   value="<?= htmlspecialchars($old['category'] ?? '') ?>"
                                   placeholder="π.χ. Εστίαση, Λιανικό, Υπηρεσίες">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Πόλη</label>
                            <input type="text" name="city" class="form-control"
                                   value="<?= htmlspecialchars($old['city'] ?? '') ?>"
                                   placeholder="π.χ. Αθήνα">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Χώρα</label>
                            <input type="text" name="country" class="form-control"
                                   value="<?= htmlspecialchars($old['country'] ?? 'Ελλάδα') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Διεύθυνση</label>
                            <input type="text" name="address" class="form-control"
                                   value="<?= htmlspecialchars($old['address'] ?? '') ?>"
                                   placeholder="Οδός, αριθμός, ΤΚ">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Σημειώσεις</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Πρώτη εντύπωση, ώρα επικοινωνίας, κ.λπ."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                        </div>

                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Αποθήκευση Lead
                        </button>
                        <a href="<?= APP_URL ?>/caller/businesses" class="btn btn-outline-secondary">Ακύρωση</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
