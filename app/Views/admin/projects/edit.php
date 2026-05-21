<?php use App\Core\CSRF;
require_once __DIR__ . '/../../_partials/gr_helpers.php';
?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pencil me-1"></i> Επεξεργασία Έργου</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/projects/<?= $project['id'] ?>/update">
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Τίτλος Έργου *</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($project['title']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Περιγραφή</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Κατάσταση</label>
                            <select name="status" class="form-select">
                                <?php foreach (['awaiting_assignment','in_progress','testing','on_hold','completed'] as $s): ?>
                                <option value="<?= $s ?>" <?= $project['status']===$s?'selected':'' ?>><?= grStatus($s) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Προτεραιότητα</label>
                            <select name="priority" class="form-select">
                                <?php foreach (['low','medium','high','urgent'] as $p): ?>
                                <option value="<?= $p ?>" <?= $project['priority']===$p?'selected':'' ?>><?= grPriority($p) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Προϋπολογισμός (€)</label>
                            <input type="number" name="budget" class="form-control" step="0.01" value="<?= $project['budget'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ημερομηνία Έναρξης</label>
                            <input type="date" name="start_date" class="form-control" value="<?= $project['start_date'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Προθεσμία</label>
                            <input type="date" name="deadline" class="form-control" value="<?= $project['deadline'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Τεχνολογίες</label>
                            <input type="text" name="tech_stack" class="form-control" value="<?= htmlspecialchars($project['tech_stack'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">URL Αποθετηρίου</label>
                            <input type="url" name="repo_url" class="form-control" value="<?= htmlspecialchars($project['repo_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">URL Staging</label>
                            <input type="url" name="staging_url" class="form-control" value="<?= htmlspecialchars($project['staging_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Live URL</label>
                            <input type="url" name="live_url" class="form-control" value="<?= htmlspecialchars($project['live_url'] ?? '') ?>">
                        </div>
                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <a href="<?= APP_URL ?>/admin/projects/<?= $project['id'] ?>" class="btn btn-outline-secondary">Ακύρωση</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Αποθήκευση Αλλαγών</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
