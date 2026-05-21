<?php use App\Core\CSRF; ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-kanban me-1"></i> Δημιουργία Έργου από Συμφωνία</div>
            <div class="card-body">
                <!-- Στοιχεία Συμφωνίας -->
                <div class="alert alert-info mb-4">
                    <strong>Συμφωνία:</strong> <?= htmlspecialchars($deal['company_name']) ?> —
                    €<?= number_format($deal['amount'],2) ?> |
                    <strong>Τηλεφωνητής:</strong> <?= htmlspecialchars($deal['caller_name']) ?>
                </div>

                <form method="POST" action="<?= APP_URL ?>/admin/projects">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="deal_id" value="<?= $deal['id'] ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Τίτλος Έργου *</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars('Έργο για ' . $deal['company_name']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Περιγραφή</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Αντικείμενο και στόχοι έργου..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ανάθεση Προγραμματιστή</label>
                            <select name="developer_id" class="form-select">
                                <option value="">— Χωρίς Ανάθεση —</option>
                                <?php foreach ($developers as $dev): ?>
                                <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Προτεραιότητα</label>
                            <select name="priority" class="form-select">
                                <option value="low">Χαμηλή</option>
                                <option value="medium" selected>Μέτρια</option>
                                <option value="high">Υψηλή</option>
                                <option value="urgent">Επείγον</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Προϋπολογισμός (€)</label>
                            <input type="number" name="budget" class="form-control" step="0.01" value="<?= $deal['amount'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ημερομηνία Έναρξης</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Προθεσμία</label>
                            <input type="date" name="deadline" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Τεχνολογίες</label>
                            <input type="text" name="tech_stack" class="form-control" placeholder="π.χ. PHP, Laravel, Vue.js">
                        </div>

                        <!-- Προεπιλεγμένες Φάσεις -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Φάσεις για Δημιουργία</label>
                            <div class="row g-2">
                                <?php
                                $phases = [
                                    'requirements' => 'Απαιτήσεις & Σχεδιασμός',
                                    'design'       => 'Σχεδιασμός UI/UX',
                                    'development'  => 'Ανάπτυξη',
                                    'testing'      => 'Δοκιμές & QA',
                                    'deployment'   => 'Ανάπτυξη/Εγκατάσταση',
                                    'handover'     => 'Παράδοση στον Πελάτη',
                                ];
                                foreach ($phases as $key => $label): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="default_phases[]" value="<?= $key ?>" id="phase_<?= $key ?>" class="form-check-input" checked>
                                        <label for="phase_<?= $key ?>" class="form-check-label"><?= $label ?></label>
                                    </div>
                                </div>
                                <?php endforeach ?>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <a href="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>" class="btn btn-outline-secondary">Ακύρωση</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Δημιουργία Έργου</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
