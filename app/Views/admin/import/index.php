<?php use App\Core\CSRF; ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-file-earmark-excel me-1 text-success"></i> Import Businesses from Excel</div>
            <div class="card-body">
                <div class="alert alert-info small">
                    <strong>Supported formats:</strong> .xlsx, .xls, .csv<br>
                    <strong>Required columns:</strong> Company Name / Business Name<br>
                    <strong>Optional columns:</strong> Contact Name, Email, Phone, Website, City, Country, Category, Address, Notes<br>
                    Column headers are auto-detected (case-insensitive).
                </div>

                <form method="POST" action="<?= APP_URL ?>/admin/import/preview" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Excel / CSV File</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Max size: <?= UPLOAD_MAX_SIZE / 1048576 ?>MB</div>
                    </div>
                    <button class="btn btn-success w-100"><i class="bi bi-eye me-1"></i>Preview Import</button>
                </form>
            </div>
        </div>

        <!-- Template Download -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-download me-1"></i> Download Template</div>
            <div class="card-body">
                <p class="text-muted small mb-2">Use this template to prepare your Excel file:</p>
                <a href="<?= APP_URL ?>/assets/templates/businesses_template.xlsx" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Download Template (.xlsx)</a>
            </div>
        </div>
    </div>
</div>
