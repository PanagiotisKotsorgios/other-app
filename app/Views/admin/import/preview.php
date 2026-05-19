<?php use App\Core\CSRF; ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold"><i class="bi bi-table me-1"></i> Import Preview</span>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-secondary fs-6"><?= number_format($totalRows) ?> total rows</span>
            <?php $valid = count(array_filter($preview, fn($r) => empty($r['errors']))); ?>
            <span class="badge bg-success"><?= $valid ?> valid (shown)</span>
            <?php $invalid = count(array_filter($preview, fn($r) => !empty($r['errors']))); ?>
            <?php if ($invalid): ?><span class="badge bg-danger"><?= $invalid ?> errors</span><?php endif ?>
        </div>
    </div>
    <div class="card-body pb-2 pt-2">
        <div class="alert alert-info py-2 mb-2 small">
            <i class="bi bi-info-circle me-1"></i>
            Showing first <strong><?= count($preview) ?></strong> of <strong><?= number_format($totalRows) ?></strong> data rows.
            <?php if ($totalRows > count($preview)): ?>
            All <strong><?= number_format($totalRows) ?></strong> rows will be imported — only the first 200 are previewed.
            <?php endif ?>
            <?php if (!empty($colMap)): ?>
            <br>Detected columns:
            <?php foreach ($colMap as $field => $col): ?>
                <span class="badge bg-secondary ms-1"><?= $col ?>→<?= htmlspecialchars(ucfirst(str_replace('_',' ',$field))) ?></span>
            <?php endforeach ?>
            <?php else: ?>
            <span class="text-danger fw-semibold">⚠ No columns auto-detected — check your file headers.</span>
            <?php endif ?>
        </div>
        <div class="table-responsive" style="max-height:380px;overflow-y:auto">
            <table class="table table-sm table-bordered align-middle mb-0" style="font-size:.82rem">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th width="45">#</th>
                        <th>Company Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Category</th>
                        <th width="80">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($preview as $r): ?>
                <tr class="<?= $r['errors'] ? 'table-danger' : '' ?>">
                    <td class="text-muted small"><?= $r['row'] ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($r['data']['company_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['data']['contact_name'] ?? '') ?></td>
                    <td class="small"><?= htmlspecialchars($r['data']['email'] ?? '') ?></td>
                    <td class="small"><?= htmlspecialchars($r['data']['phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['data']['city'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['data']['category'] ?? '') ?></td>
                    <td class="text-center">
                        <?php if ($r['errors']): ?>
                            <span class="badge bg-danger" title="<?= htmlspecialchars(implode(', ', $r['errors'])) ?>">Error</span>
                        <?php else: ?>
                            <span class="badge bg-success">OK</span>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
                <?php if (empty($preview)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">No data rows found in file.</td></tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white">
        <?php if (empty($colMap)): ?>
            <div class="alert alert-warning mb-3">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Column mapping failed.</strong> Your file headers were not recognized.
                Please ensure your Excel has a header row with column names like:
                <em>Company Name, Email, Phone, City, Category</em> (or Greek equivalents like <em>ΕΠΩΝΥΜΙΑ, ΤΗΛΕΦΩΝΟ, ΠΟΛΗ</em>).
                <br><a href="<?= APP_URL ?>/admin/import" class="btn btn-sm btn-outline-secondary mt-2">Try Again</a>
            </div>
        <?php else: ?>
        <form method="POST" action="<?= APP_URL ?>/admin/import/run" id="importForm">
            <?= CSRF::field() ?>
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Assign All To Caller <small class="text-muted fw-normal">(optional)</small></label>
                    <select name="caller_id" class="form-select">
                        <option value="">— Leave unassigned —</option>
                        <?php foreach ($callers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success" id="importBtn">
                        <i class="bi bi-cloud-upload me-1"></i>
                        Import All <?= number_format($totalRows) ?> Rows
                    </button>
                    <a href="<?= APP_URL ?>/admin/import" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
        <?php endif ?>
    </div>
</div>

<script>
document.getElementById('importForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('importBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing <?= number_format($totalRows) ?> rows… please wait';
});
</script>
