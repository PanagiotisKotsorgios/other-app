<?php require_once __DIR__ . '/../../_partials/gr_helpers.php'; ?>
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle-lg"><?= strtoupper(substr($caller['name'],0,1)) ?></div>
            <div>
                <h4 class="mb-0"><?= htmlspecialchars($caller['name']) ?></h4>
                <span class="text-muted"><?= htmlspecialchars($caller['email']) ?></span>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="<?= APP_URL ?>/admin/callers/<?= $caller['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Επεξεργασία</a>
                <a href="<?= APP_URL ?>/admin/messages/compose?to=<?= $caller['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-envelope me-1"></i>Μήνυμα</a>
            </div>
        </div>
    </div>

    <!-- Κάρτες Στατιστικών -->
    <div class="col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-icon"><i class="bi bi-building"></i></div><div class="kpi-value"><?= $stats['assigned_businesses']??0 ?></div><div class="kpi-label">Ανατεθειμένες</div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-icon"><i class="bi bi-telephone"></i></div><div class="kpi-value"><?= $stats['total_calls']??0 ?></div><div class="kpi-label">Κλήσεις</div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-teal"><div class="kpi-icon"><i class="bi bi-envelope"></i></div><div class="kpi-value"><?= $stats['total_emails']??0 ?></div><div class="kpi-label">Emails</div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-green"><div class="kpi-icon"><i class="bi bi-bag-check"></i></div><div class="kpi-value"><?= $stats['deals_approved']??0 ?></div><div class="kpi-label">Εγκεκριμένες Συμφωνίες</div></div></div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Σύνοψη Συμφωνιών</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Εκκρεμείς Συμφωνίες</dt><dd class="col-6"><?= $stats['deals_pending']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Εγκεκριμένες</dt><dd class="col-6 text-success fw-semibold"><?= $stats['deals_approved']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Σε Εξέλιξη</dt><dd class="col-6"><?= $stats['deals_in_progress']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Συνολικά Έσοδα</dt><dd class="col-6 fw-bold">€<?= number_format($stats['total_revenue']??0,2) ?></dd>
                    <dt class="col-6 text-muted fw-normal">Οφειλόμενη Προμήθεια</dt><dd class="col-6 fw-bold text-danger">€<?= number_format($stats['commission_owed']??0,2) ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Σύνοψη Αλληλεπιδράσεων</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Κλήσεις</dt><dd class="col-6"><?= $stats['total_calls']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Emails</dt><dd class="col-6"><?= $stats['total_emails']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Προσφορές</dt><dd class="col-6"><?= $stats['total_offers']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Παρουσιάσεις</dt><dd class="col-6"><?= $stats['total_demos']??0 ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12">
        <a href="<?= APP_URL ?>/admin/callers" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Επιστροφή στους Τηλεφωνητές</a>
    </div>
</div>
