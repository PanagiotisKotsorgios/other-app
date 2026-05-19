<?php use App\Core\{CSRF, Session}; $old = Session::getFlash('old', []); $errors = Session::getFlash('errors', []); ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-plus-circle me-1"></i> Add New Business</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/businesses">
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control <?= isset($errors['company_name'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($old['company_name']??'') ?>" required>
                            <?php if(isset($errors['company_name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['company_name']) ?></div><?php endif ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($old['contact_name']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($old['phone']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($old['website']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($old['category']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($old['city']??'') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($old['country']??'') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($old['address']??'') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($old['notes']??'') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign to Caller (optional)</label>
                            <select name="caller_id" class="form-select">
                                <option value="">— Not assigned —</option>
                                <?php foreach ($callers as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">Save Business</button>
                        <a href="<?= APP_URL ?>/admin/businesses" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
