<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle-lg"><?= strtoupper(substr($caller['name'],0,1)) ?></div>
            <div>
                <h4 class="mb-0"><?= htmlspecialchars($caller['name']) ?></h4>
                <span class="text-muted"><?= htmlspecialchars($caller['email']) ?></span>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="<?= APP_URL ?>/admin/callers/<?= $caller['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
                <a href="<?= APP_URL ?>/admin/messages/compose?to=<?= $caller['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-envelope me-1"></i>Message</a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-icon"><i class="bi bi-building"></i></div><div class="kpi-value"><?= $stats['assigned_businesses']??0 ?></div><div class="kpi-label">Assigned</div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-icon"><i class="bi bi-telephone"></i></div><div class="kpi-value"><?= $stats['total_calls']??0 ?></div><div class="kpi-label">Calls</div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-teal"><div class="kpi-icon"><i class="bi bi-envelope"></i></div><div class="kpi-value"><?= $stats['total_emails']??0 ?></div><div class="kpi-label">Emails</div></div></div>
    <div class="col-md-3"><div class="kpi-card kpi-green"><div class="kpi-icon"><i class="bi bi-bag-check"></i></div><div class="kpi-value"><?= $stats['deals_approved']??0 ?></div><div class="kpi-label">Deals Approved</div></div></div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Deal Summary</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Pending Deals</dt><dd class="col-6"><?= $stats['deals_pending']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Approved</dt><dd class="col-6 text-success fw-semibold"><?= $stats['deals_approved']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">In Progress</dt><dd class="col-6"><?= $stats['deals_in_progress']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Total Revenue</dt><dd class="col-6 fw-bold">€<?= number_format($stats['total_revenue']??0,2) ?></dd>
                    <dt class="col-6 text-muted fw-normal">Commission Owed</dt><dd class="col-6 fw-bold text-danger">€<?= number_format($stats['commission_owed']??0,2) ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Interaction Summary</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted fw-normal">Calls</dt><dd class="col-6"><?= $stats['total_calls']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Emails</dt><dd class="col-6"><?= $stats['total_emails']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Offers</dt><dd class="col-6"><?= $stats['total_offers']??0 ?></dd>
                    <dt class="col-6 text-muted fw-normal">Demos</dt><dd class="col-6"><?= $stats['total_demos']??0 ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12">
        <a href="<?= APP_URL ?>/admin/callers" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Callers</a>
    </div>
</div>
