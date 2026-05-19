<?php use App\Core\{CSRF, Session}; $old = Session::getFlash('old',[]); $errors = Session::getFlash('errors',[]); ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bag-plus me-1 text-success"></i>Submit Deal for <?= htmlspecialchars($business['company_name']) ?></div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/caller/deals" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="business_id" value="<?= $business['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Service / Product <span class="text-danger">*</span></label>
                        <select name="service_id" class="form-select" required>
                            <option value="">— Select Service —</option>
                            <?php foreach ($services as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deal Amount (€) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" name="amount" id="amount" class="form-control <?= isset($errors['amount'])?'is-invalid':'' ?>" step="0.01" min="0.01" value="<?= htmlspecialchars($old['amount']??'') ?>" required>
                            <?php if(isset($errors['amount'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['amount']) ?></div><?php endif ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="alert alert-success py-2">
                            <small>Your commission (<?= COMMISSION_RATE ?>%): <strong id="commPreview">€0.00</strong></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($old['notes']??'') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Upload Proposal (optional)</label>
                        <input type="file" name="proposal_file" class="form-control" accept=".pdf,.doc,.docx">
                        <div class="form-text">PDF or DOC only, max <?= UPLOAD_MAX_SIZE/1048576 ?>MB</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-success"><i class="bi bi-send me-1"></i>Submit Deal</button>
                        <a href="<?= APP_URL ?>/caller/businesses/<?= $business['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('amount').addEventListener('input', function(){
    const amt = parseFloat(this.value)||0;
    document.getElementById('commPreview').textContent = '€'+(amt*<?= COMMISSION_RATE ?>/100).toFixed(2);
});
</script>
