<?php use App\Core\CSRF; ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-file-earmark-excel me-1 text-success"></i> Εισαγωγή Επιχειρήσεων από Excel</div>
            <div class="card-body">
                <div class="alert alert-info small">
                    <strong>Υποστηριζόμενες μορφές:</strong> .xlsx, .xls, .csv<br>
                    <strong>Υποχρεωτικές στήλες:</strong> Επωνυμία / Όνομα Επιχείρησης<br>
                    <strong>Προαιρετικές στήλες:</strong> Όνομα Επαφής, Email, Τηλέφωνο, Ιστοσελίδα, Πόλη, Χώρα, Κατηγορία, Διεύθυνση, Σημειώσεις<br>
                    Οι επικεφαλίδες στηλών εντοπίζονται αυτόματα (χωρίς διάκριση κεφαλαίων).
                </div>

                <form method="POST" action="<?= APP_URL ?>/admin/import/preview" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Επιλογή Αρχείου Excel / CSV</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Μέγιστο μέγεθος: <?= UPLOAD_MAX_SIZE / 1048576 ?>MB</div>
                    </div>
                    <button class="btn btn-success w-100"><i class="bi bi-eye me-1"></i>Προεπισκόπηση Εισαγωγής</button>
                </form>
            </div>
        </div>

        <!-- Λήψη Προτύπου -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-download me-1"></i> Λήψη Προτύπου</div>
            <div class="card-body">
                <p class="text-muted small mb-2">Χρησιμοποιήστε αυτό το πρότυπο για να προετοιμάσετε το αρχείο Excel σας:</p>
                <a href="<?= APP_URL ?>/assets/templates/businesses_template.xlsx" class="btn btn-outline-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Λήψη Προτύπου (.xlsx)</a>
            </div>
        </div>
    </div>
</div>
