<?php require_once __DIR__ . '/../../_partials/gr_helpers.php'; ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Αναζήτηση..." value="<?= htmlspecialchars($filters['search']) ?>"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Όλες οι Καταστάσεις</option>
                    <?php foreach(['pending','approved','rejected','in_progress','completed'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= grStatus($s) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Φίλτρο</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between">
        <span>Οι Συμφωνίες Μου</span><span class="badge bg-primary"><?= $total ?></span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Επιχείρηση</th><th>Υπηρεσία</th><th>Ποσό</th><th>Προμήθεια (<?= COMMISSION_RATE ?>%)</th><th>Κατάσταση</th><th>Ημερομηνία</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $d): ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($d['company_name']) ?></td>
                <td><?= htmlspecialchars($d['service_name']??'—') ?></td>
                <td class="fw-bold">€<?= number_format($d['amount'],2) ?></td>
                <td class="text-success fw-semibold">€<?= number_format($d['amount']*COMMISSION_RATE/100,2) ?></td>
                <td><span class="badge <?= match($d['status']){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>"><?= grStatus($d['status']) ?></span></td>
                <td class="small text-muted"><?= date('d M Y', strtotime($d['created_at'])) ?></td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="6" class="text-center py-4 text-muted">Δεν έχουν υποβληθεί συμφωνίες.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if($last_page>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/../../_partials/pagination.php' ?></div><?php endif ?>
</div>
