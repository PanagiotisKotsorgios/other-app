<?php
use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
$isOverdue = $project['deadline'] && $project['deadline'] < date('Y-m-d') && !in_array($project['status'], ['completed','on_hold']);
$phaseDone = count(array_filter($phases, fn($p) => $p['status']==='completed'));
$phaseTotal = count($phases);
$pct = $phaseTotal > 0 ? round($phaseDone/$phaseTotal*100) : 0;
?>

<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <a href="<?= APP_URL ?>/developer/projects" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Όλα τα Έργα</a>
</div>

<?php if($isOverdue): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Το έργο είναι <strong>εκπρόθεσμο</strong>! Η προθεσμία ήταν <?= date('d M Y', strtotime($project['deadline'])) ?>.</div>
<?php endif ?>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Στοιχεία Έργου -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-kanban me-1"></i> <?= htmlspecialchars($project['title']) ?></span>
                <span class="badge bg-<?= match($project['status']){'awaiting_assignment'=>'secondary','in_progress'=>'primary','testing'=>'info','on_hold'=>'warning','completed'=>'success',default=>'secondary'} ?> fs-6">
                    <?= grStatus($project['status']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><th class="text-muted fw-normal">Επιχείρηση</th><td class="fw-semibold"><?= htmlspecialchars($project['company_name']) ?></td></tr>
                            <tr><th class="text-muted fw-normal">Προτεραιότητα</th><td><span class="badge <?= match($project['priority']){'low'=>'bg-light text-dark','medium'=>'bg-info text-dark','high'=>'bg-warning text-dark','urgent'=>'bg-danger',default=>'bg-secondary'} ?>"><?= grPriority($project['priority']) ?></span></td></tr>
                            <tr><th class="text-muted fw-normal">Προϋπολογισμός</th><td class="fw-bold text-success">€<?= number_format($project['budget'],2) ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><th class="text-muted fw-normal">Ημ. Έναρξης</th><td><?= $project['start_date'] ? date('d M Y', strtotime($project['start_date'])) : '—' ?></td></tr>
                            <tr><th class="text-muted fw-normal">Προθεσμία</th><td class="<?= $isOverdue?'text-danger fw-bold':'' ?>"><?= $project['deadline'] ? date('d M Y', strtotime($project['deadline'])) : '—' ?></td></tr>
                            <tr><th class="text-muted fw-normal">Τεχνολογίες</th><td><?= htmlspecialchars($project['tech_stack'] ?? '—') ?></td></tr>
                        </table>
                    </div>
                </div>
                <?php if($project['description']): ?><hr><p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($project['description'])) ?></p><?php endif ?>

                <!-- Πρόοδος Φάσεων -->
                <?php if($phaseTotal > 0): ?>
                <hr>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Πρόοδος Φάσεων</span><span><?= $phaseDone ?>/<?= $phaseTotal ?> (<?= $pct ?>%)</span>
                </div>
                <div class="progress" style="height:10px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div></div>
                <?php endif ?>
            </div>
        </div>

        <!-- Ενημέρωση Κατάστασης & URLs -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-arrow-repeat me-1"></i> Ενημέρωση Κατάστασης</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/developer/projects/<?= $project['id'] ?>/status">
                    <?= CSRF::field() ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">Κατάσταση</label>
                            <select name="status" class="form-select form-select-sm">
                                <?php foreach (['in_progress','testing','on_hold','completed'] as $s): ?>
                                <option value="<?= $s ?>" <?= $project['status']===$s?'selected':'' ?>><?= grStatus($s) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">URL Αποθετηρίου</label>
                            <input type="url" name="repo_url" class="form-control form-control-sm" value="<?= htmlspecialchars($project['repo_url'] ?? '') ?>" placeholder="https://github.com/...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">URL Staging</label>
                            <input type="url" name="staging_url" class="form-control form-control-sm" value="<?= htmlspecialchars($project['staging_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Live URL</label>
                            <input type="url" name="live_url" class="form-control form-control-sm" value="<?= htmlspecialchars($project['live_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Τεχνολογίες</label>
                            <input type="text" name="tech_stack" class="form-control form-control-sm" value="<?= htmlspecialchars($project['tech_stack'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-sm btn-primary w-100"><i class="bi bi-save me-1"></i>Ενημέρωση</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Φάσεις -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-list-check me-1"></i> Φάσεις</div>
            <div class="card-body p-0">
                <?php if(empty($phases)): ?>
                <div class="text-center text-muted py-4">Δεν έχουν οριστεί φάσεις.</div>
                <?php else: ?>
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Φάση</th><th>Κατάσταση</th><th>Προθεσμία</th><th>Ολοκλήρωση</th></tr></thead>
                    <tbody>
                    <?php foreach ($phases as $i => $phase): ?>
                        <tr>
                            <td class="text-muted small"><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($phase['name']) ?></td>
                            <td>
                                <form method="POST" action="<?= APP_URL ?>/developer/projects/phase/<?= $phase['id'] ?>/status" class="d-inline">
                                    <?= CSRF::field() ?>
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                        <?php foreach (['pending','in_progress','completed','skipped'] as $ps): ?>
                                        <option value="<?= $ps ?>" <?= $phase['status']===$ps?'selected':'' ?>><?= grStatus($ps) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </form>
                            </td>
                            <td><?= $phase['due_date'] ? date('d M Y', strtotime($phase['due_date'])) : '—' ?></td>
                            <td><?= $phase['completed_at'] ? date('d M Y', strtotime($phase['completed_at'])) : '—' ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>

        <!-- Σημειώσεις -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-chat-text me-1"></i> Σημειώσεις</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/projects/note/add" class="mb-3">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    <div class="d-flex gap-2 align-items-start">
                        <textarea name="body" class="form-control" rows="2" placeholder="Προσθήκη σημείωσης..." required></textarea>
                        <button class="btn btn-primary btn-sm">Δημοσίευση</button>
                    </div>
                </form>
                <?php foreach ($notes as $note): ?>
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <strong class="small"><?= htmlspecialchars($note['author_name']) ?></strong>
                        <span class="text-muted small"><?= date('d M Y H:i', strtotime($note['created_at'])) ?></span>
                    </div>
                    <p class="mb-0 small"><?= nl2br(htmlspecialchars($note['body'])) ?></p>
                </div>
                <?php endforeach ?>
                <?php if(empty($notes)): ?><p class="text-muted mb-0 small">Δεν υπάρχουν σημειώσεις.</p><?php endif ?>
            </div>
        </div>
    </div>

    <!-- Δεξιά Στήλη -->
    <div class="col-lg-4">
        <!-- Συμβάσεις -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-file-earmark-pdf me-1"></i> Συμβάσεις</div>
            <div class="card-body p-0">
                <?php if(empty($contracts)): ?>
                <div class="text-center text-muted py-3 small">Δεν υπάρχουν συμβάσεις.</div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($contracts as $c): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                            <small><?= htmlspecialchars($c['original_name']) ?></small>
                        </div>
                        <a href="<?= APP_URL ?>/admin/documents/contracts/<?= $c['id'] ?>/download" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
        </div>

        <!-- Τιμολόγια -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-receipt me-1"></i> Τιμολόγια</div>
            <div class="card-body p-0">
                <?php if(empty($invoices)): ?>
                <div class="text-center text-muted py-3 small">Δεν υπάρχουν τιμολόγια.</div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($invoices as $inv): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span class="small"><?= htmlspecialchars($inv['invoice_no'] ?? 'Τιμολόγιο') ?></span>
                            <span class="badge <?= $inv['status']==='paid'?'bg-success':'bg-warning text-dark' ?>"><?= grStatus($inv['status']) ?></span>
                        </div>
                        <div class="fw-bold">€<?= number_format($inv['total_amount'],2) ?></div>
                        <?php if($inv['filename']): ?>
                        <a href="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/download" class="btn btn-xs btn-outline-secondary" style="font-size:.7rem">Λήψη</a>
                        <?php endif ?>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
