<!-- E:\call_center\app\Views\admin\projects\create.php -->
<?php use App\Core\CSRF; ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-kanban me-1"></i> Create Project from Deal</div>
            <div class="card-body">
                <!-- Deal context -->
                <div class="alert alert-info mb-4">
                    <strong>Deal:</strong> <?= htmlspecialchars($deal['company_name']) ?> —
                    €<?= number_format($deal['amount'],2) ?> |
                    <strong>Caller:</strong> <?= htmlspecialchars($deal['caller_name']) ?>
                </div>

                <form method="POST" action="<?= APP_URL ?>/admin/projects">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="deal_id" value="<?= $deal['id'] ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Project Title *</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars('Project for ' . $deal['company_name']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Project scope and objectives..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign Developer</label>
                            <select name="developer_id" class="form-select">
                                <option value="">— Unassigned (awaiting assignment) —</option>
                                <?php foreach ($developers as $dev): ?>
                                <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Budget (€)</label>
                            <input type="number" name="budget" class="form-control" step="0.01" value="<?= $deal['amount'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Deadline</label>
                            <input type="date" name="deadline" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tech Stack</label>
                            <input type="text" name="tech_stack" class="form-control" placeholder="e.g. PHP, Laravel, Vue.js">
                        </div>

                        <!-- Default Phases -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Default Phases to Create</label>
                            <div class="row g-2">
                                <?php
                                $phases = [
                                    'requirements' => 'Requirements & Planning',
                                    'design'       => 'UI/UX Design',
                                    'development'  => 'Development',
                                    'testing'      => 'Testing & QA',
                                    'deployment'   => 'Deployment',
                                    'handover'     => 'Client Handover',
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
                            <a href="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Project</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
