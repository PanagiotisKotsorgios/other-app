<?php
use App\Core\CSRF;
$isOverdue = $project['deadline'] && $project['deadline'] < date('Y-m-d') && !in_array($project['status'], ['completed','on_hold']);
$colorMap = ['green'=>'#166534','blue'=>'#1d4ed8','orange'=>'#c2410c','red'=>'#b91c1c','purple'=>'#7e22ce','teal'=>'#0f766e'];
$bgMap    = ['green'=>'#dcfce7','blue'=>'#dbeafe','orange'=>'#fff7ed','red'=>'#fee2e2','purple'=>'#f3e8ff','teal'=>'#ccfbf1'];
$statusColors = ['awaiting_assignment'=>'secondary','in_progress'=>'primary','testing'=>'info','on_hold'=>'warning','completed'=>'success'];
$phaseDone = count(array_filter($phases, fn($p) => $p['status']==='completed'));
$phaseTotal = count($phases);
$pct = $phaseTotal > 0 ? round($phaseDone/$phaseTotal*100) : 0;

// Financial totals
$totalExpAmt = array_sum(array_column($expenses, 'amount'));
$dealAmnt    = (float)($project['deal_amount'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <a href="<?= APP_URL ?>/admin/projects" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>All Projects</a>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/admin/projects/<?= $project['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
</div>

<?php if($isOverdue): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>This project is <strong>overdue</strong>! Deadline was <?= date('d M Y', strtotime($project['deadline'])) ?>.</div>
<?php endif ?>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Project Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-kanban me-1"></i> Project Details</span>
                <span class="badge bg-<?= $statusColors[$project['status']] ?? 'secondary' ?> fs-6">
                    <?= ucfirst(str_replace('_',' ',$project['status'])) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><th class="text-muted fw-normal w-40">Business</th><td class="fw-semibold"><?= htmlspecialchars($project['company_name']) ?></td></tr>
                            <tr><th class="text-muted fw-normal">City</th><td><?= htmlspecialchars($project['city'] ?? '—') ?></td></tr>
                            <tr><th class="text-muted fw-normal">Caller</th><td><?= htmlspecialchars($project['caller_name']) ?></td></tr>
                            <tr><th class="text-muted fw-normal">Developer</th><td><?= htmlspecialchars($project['developer_name'] ?? '—') ?></td></tr>
                            <tr><th class="text-muted fw-normal">Priority</th><td><span class="badge <?= match($project['priority']){'low'=>'bg-light text-dark','medium'=>'bg-info text-dark','high'=>'bg-warning text-dark','urgent'=>'bg-danger',default=>'bg-secondary'} ?>"><?= ucfirst($project['priority']) ?></span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><th class="text-muted fw-normal w-40">Budget</th><td class="fw-bold text-success fs-5">€<?= number_format($project['budget'],2) ?></td></tr>
                            <tr><th class="text-muted fw-normal">Start Date</th><td><?= $project['start_date'] ? date('d M Y', strtotime($project['start_date'])) : '—' ?></td></tr>
                            <tr><th class="text-muted fw-normal">Deadline</th><td><?= $project['deadline'] ? date('d M Y', strtotime($project['deadline'])) : '—' ?></td></tr>
                            <tr><th class="text-muted fw-normal">Completed</th><td><?= $project['actual_end'] ? date('d M Y', strtotime($project['actual_end'])) : '—' ?></td></tr>
                            <tr><th class="text-muted fw-normal">Contract</th><td><?= $project['contract_signed'] ? '<span class="badge bg-success">Signed</span>' : '<span class="badge bg-warning text-dark">Pending</span>' ?></td></tr>
                        </table>
                    </div>
                </div>
                <?php if($project['description']): ?>
                <hr><p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                <?php endif ?>
                <?php if($project['tech_stack']): ?>
                <hr><strong>Tech Stack:</strong> <?= htmlspecialchars($project['tech_stack']) ?>
                <?php endif ?>

                <!-- Phase Progress -->
                <?php if($phaseTotal > 0): ?>
                <hr>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Phase Progress</span><span><?= $phaseDone ?>/<?= $phaseTotal ?> (<?= $pct ?>%)</span>
                </div>
                <div class="progress" style="height:10px">
                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                </div>
                <?php endif ?>

                <!-- URLs -->
                <?php if($project['repo_url'] || $project['staging_url'] || $project['live_url']): ?>
                <hr>
                <div class="d-flex flex-wrap gap-2">
                    <?php if($project['repo_url']): ?><a href="<?= htmlspecialchars($project['repo_url']) ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-github me-1"></i>Repository</a><?php endif ?>
                    <?php if($project['staging_url']): ?><a href="<?= htmlspecialchars($project['staging_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-link-45deg me-1"></i>Staging</a><?php endif ?>
                    <?php if($project['live_url']): ?><a href="<?= htmlspecialchars($project['live_url']) ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-globe me-1"></i>Live</a><?php endif ?>
                </div>
                <?php endif ?>
            </div>
        </div>

        <!-- Phases Timeline -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-list-check me-1"></i> Phases</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPhaseModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Phase
                </button>
            </div>
            <div class="card-body p-0">
                <?php if(empty($phases)): ?>
                <div class="text-center text-muted py-4">No phases defined yet.</div>
                <?php else: ?>
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Phase</th><th>Status</th><th>Due Date</th><th>Completed</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($phases as $i => $phase): ?>
                        <tr>
                            <td class="text-muted small"><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($phase['name']) ?></td>
                            <td>
                                <form method="POST" action="<?= APP_URL ?>/admin/projects/phase/<?= $phase['id'] ?>/update" class="d-inline">
                                    <?= CSRF::field() ?>
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                        <?php foreach (['pending','in_progress','completed','skipped'] as $ps): ?>
                                        <option value="<?= $ps ?>" <?= $phase['status']===$ps?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$ps)) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </form>
                            </td>
                            <td><?= $phase['due_date'] ? date('d M Y', strtotime($phase['due_date'])) : '—' ?></td>
                            <td><?= $phase['completed_at'] ? date('d M Y', strtotime($phase['completed_at'])) : '—' ?></td>
                            <td>
                                <form method="POST" action="<?= APP_URL ?>/admin/projects/phase/<?= $phase['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete phase?')">
                                    <?= CSRF::field() ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>

        <!-- Notes -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-chat-text me-1"></i> Notes</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/projects/note/add" class="mb-3">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    <div class="d-flex gap-2 align-items-start">
                        <textarea name="body" class="form-control" rows="2" placeholder="Add a note..." required></textarea>
                        <div>
                            <button class="btn btn-primary btn-sm mb-1 w-100">Post</button>
                            <div class="form-check ms-1">
                                <input type="checkbox" name="is_internal" value="1" id="is_internal_cb" class="form-check-input">
                                <label for="is_internal_cb" class="form-check-label small">Internal</label>
                            </div>
                        </div>
                    </div>
                </form>
                <?php foreach ($notes as $note): ?>
                <div class="border rounded p-2 mb-2 <?= $note['is_internal'] ? 'bg-light border-warning' : '' ?>">
                    <div class="d-flex justify-content-between mb-1">
                        <strong class="small"><?= htmlspecialchars($note['author_name']) ?></strong>
                        <span class="text-muted small"><?= date('d M Y H:i', strtotime($note['created_at'])) ?> <?= $note['is_internal'] ? '<span class="badge bg-warning text-dark ms-1">Internal</span>' : '' ?></span>
                    </div>
                    <p class="mb-0 small"><?= nl2br(htmlspecialchars($note['body'])) ?></p>
                </div>
                <?php endforeach ?>
                <?php if(empty($notes)): ?><p class="text-muted mb-0 small">No notes yet.</p><?php endif ?>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Assign Developer -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-badge me-1"></i> Lead Developer</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/projects/<?= $project['id'] ?>/assign-developer">
                    <?= CSRF::field() ?>
                    <select name="developer_id" class="form-select mb-2">
                        <option value="">— Unassigned —</option>
                        <?php foreach ($developers as $dev): ?>
                        <option value="<?= $dev['id'] ?>" <?= $project['developer_id']==$dev['id']?'selected':'' ?>><?= htmlspecialchars($dev['name']) ?></option>
                        <?php endforeach ?>
                    </select>
                    <button class="btn btn-sm btn-primary w-100"><i class="bi bi-person-check me-1"></i>Assign</button>
                </form>
            </div>
        </div>

        <!-- Team Assignments -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
                <span><i class="bi bi-people me-1"></i> Team Assignments</span>
                <button class="btn btn-xs btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addAssignmentForm">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            <div class="collapse" id="addAssignmentForm">
                <form method="POST" action="<?= APP_URL ?>/admin/projects/<?= $project['id'] ?>/assign-user"
                      class="p-3 border-bottom">
                    <?= CSRF::field() ?>
                    <div class="mb-2">
                        <label class="form-label text-xs fw-600">User</label>
                        <select name="user_id" class="form-select form-select-sm" required>
                            <option value="">— Select user —</option>
                            <?php
                            $assignedIds = array_column($assignments ?? [], 'user_id');
                            foreach ($allUsers as $u):
                                if (in_array($u['id'], $assignedIds)) continue;
                            ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= ucfirst($u['role']) ?>)</option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-xs fw-600">Role on Project</label>
                        <select name="role_type" class="form-select form-select-sm">
                            <option value="developer">Developer</option>
                            <option value="caller">Caller</option>
                            <option value="partner">Partner</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-xs fw-600">Notes</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional notes…">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-person-plus me-1"></i>Add to Team
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <?php if (empty($assignments)): ?>
                <div class="text-center text-muted py-3 small">No team members assigned yet.</div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php
                    $roleIcons = ['developer'=>'bi-code-slash','caller'=>'bi-telephone','partner'=>'bi-handshake'];
                    $roleColors = ['developer'=>'bg-primary','caller'=>'bg-success','partner'=>'bg-purple'];
                    foreach ($assignments as $a):
                    ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle flex-shrink-0" style="width:30px;height:30px;font-size:.75rem">
                                <?= strtoupper(substr($a['user_name'],0,1)) ?>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-600 text-sm text-truncate"><?= htmlspecialchars($a['user_name']) ?></div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-secondary" style="font-size:.68rem">
                                        <i class="bi <?= $roleIcons[$a['role_type']] ?? 'bi-person' ?> me-1"></i><?= ucfirst($a['role_type']) ?>
                                    </span>
                                    <?php if ($a['category_name']): ?>
                                    <span class="badge text-xs" style="font-size:.65rem;background:<?= $bgMap[$a['category_color'] ?? 'blue'] ?? '#dbeafe' ?>;color:<?= $colorMap[$a['category_color'] ?? 'blue'] ?? '#1d4ed8' ?>">
                                        <?= htmlspecialchars($a['category_name']) ?>
                                    </span>
                                    <?php endif ?>
                                </div>
                                <?php if ($a['notes']): ?><div class="text-xs text-muted mt-1"><?= htmlspecialchars($a['notes']) ?></div><?php endif ?>
                            </div>
                            <form method="POST" action="<?= APP_URL ?>/admin/projects/assignment/<?= $a['id'] ?>/remove"
                                  onsubmit="return confirm('Remove from team?')" class="flex-shrink-0">
                                <?= CSRF::field() ?>
                                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                <button class="btn btn-xs btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </div>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
        </div>

        <!-- Financial Breakdown -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-currency-euro me-1"></i> Financial Summary</div>
            <div class="card-body">
                <?php
                $commTotal = $dealAmnt * 0.10;
                $devComm   = $dealAmnt * 0.20;
                $net       = $dealAmnt - $totalExpAmt - $commTotal - ($project['developer_id'] ? $devComm : 0);
                ?>
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">Deal Amount</td><td class="fw-bold text-success text-end">€<?= number_format($dealAmnt,2) ?></td></tr>
                    <tr><td class="text-muted">Caller Commission (10%)</td><td class="text-warning text-end">-€<?= number_format($commTotal,2) ?></td></tr>
                    <?php if($project['developer_id']): ?>
                    <tr><td class="text-muted">Dev Commission (20%)</td><td class="text-warning text-end">-€<?= number_format($devComm,2) ?></td></tr>
                    <?php endif ?>
                    <tr><td class="text-muted">Expenses</td><td class="text-danger text-end">-€<?= number_format($totalExpAmt,2) ?></td></tr>
                    <tr class="table-light"><td class="fw-semibold">Net Profit</td><td class="fw-bold <?= $net>=0?'text-success':'text-danger' ?> text-end">€<?= number_format($net,2) ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Contracts -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-file-earmark-pdf me-1"></i> Contracts</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/documents/contracts/<?= $project['deal_id'] ?>/upload" enctype="multipart/form-data" class="mb-3">
                    <?= CSRF::field() ?>
                    <input type="file" name="contract_file" class="form-control form-control-sm mb-2" accept=".pdf,.doc,.docx" required>
                    <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Notes (optional)">
                    <button class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-upload me-1"></i>Upload Contract</button>
                </form>
                <?php if(empty($contracts)): ?>
                <p class="text-muted small mb-0">No contracts uploaded.</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($contracts as $c): ?>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                            <small><?= htmlspecialchars($c['original_name']) ?></small>
                            <div class="text-muted" style="font-size:.7rem"><?= date('d M Y', strtotime($c['uploaded_at'])) ?> by <?= htmlspecialchars($c['uploader_name']) ?></div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="<?= APP_URL ?>/admin/documents/contracts/<?= $c['id'] ?>/download" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                            <form method="POST" action="<?= APP_URL ?>/admin/documents/contracts/<?= $c['id'] ?>/delete" onsubmit="return confirm('Delete?')">
                                <?= CSRF::field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
        </div>

        <!-- Invoices -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-receipt me-1"></i> Invoices</div>
            <div class="card-body">
                <button class="btn btn-sm btn-outline-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Invoice
                </button>
                <?php if(empty($invoices)): ?>
                <p class="text-muted small mb-0">No invoices created.</p>
                <?php else: ?>
                <?php foreach ($invoices as $inv): ?>
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge <?= match($inv['status']){'draft'=>'bg-secondary','issued'=>'bg-info text-dark','sent'=>'bg-primary','paid'=>'bg-success',default=>'bg-secondary'} ?>"><?= ucfirst($inv['status']) ?></span>
                            <?php if($inv['invoice_no']): ?><strong class="ms-1 small"><?= htmlspecialchars($inv['invoice_no']) ?></strong><?php endif ?>
                        </div>
                        <strong class="text-success">€<?= number_format($inv['total_amount'],2) ?></strong>
                    </div>
                    <div class="mt-1 d-flex gap-1">
                        <?php if($inv['status']!=='paid'): ?>
                        <form method="POST" action="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/mark-paid">
                            <?= CSRF::field() ?><button class="btn btn-xs btn-outline-success" style="font-size:.7rem;padding:2px 6px">Mark Paid</button>
                        </form>
                        <?php endif ?>
                        <?php if($inv['filename']): ?>
                        <a href="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/download" class="btn btn-outline-secondary" style="font-size:.7rem;padding:2px 6px"><i class="bi bi-download"></i></a>
                        <?php endif ?>
                    </div>
                </div>
                <?php endforeach ?>
                <?php endif ?>
            </div>
        </div>

        <!-- Expenses -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-wallet2 me-1"></i> Project Expenses</div>
            <div class="card-body p-0">
                <?php if(empty($expenses)): ?>
                <div class="text-center text-muted py-3 small">No expenses recorded.</div>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <tbody>
                    <?php foreach ($expenses as $exp): ?>
                        <tr>
                            <td><small><?= htmlspecialchars($exp['description']) ?></small><br><span class="badge bg-secondary"><?= ucfirst($exp['category']) ?></span></td>
                            <td class="text-end text-danger fw-semibold">€<?= number_format($exp['amount'],2) ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
                <div class="p-2 text-end fw-bold border-top">Total: €<?= number_format($totalExpAmt,2) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Add Phase Modal -->
<div class="modal fade" id="addPhaseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/admin/projects/phase/add">
            <?= CSRF::field() ?>
            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Phase</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Phase Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="row g-2">
                        <div class="col"><label class="form-label">Order</label><input type="number" name="order_num" class="form-control" value="<?= count($phases) ?>"></div>
                        <div class="col"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Add Phase</button></div>
            </div>
        </form>
    </div>
</div>

<!-- Add Invoice Modal -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/admin/documents/invoices/<?= $project['deal_id'] ?>/upload" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Invoice No.</label><input type="text" name="invoice_no" class="form-control" placeholder="INV-001"></div>
                        <div class="col-6"><label class="form-label">Amount (€) *</label><input type="number" name="amount" class="form-control" step="0.01" required></div>
                        <div class="col-6"><label class="form-label">VAT Rate (%)</label><input type="number" name="vat_rate" class="form-control" value="24" step="0.01"></div>
                        <div class="col-6"><label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="issued">Issued</option>
                                <option value="sent">Sent</option>
                            </select>
                        </div>
                        <div class="col-6"><label class="form-label">Issue Date</label><input type="date" name="issued_at" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Due Date</label><input type="date" name="due_at" class="form-control"></div>
                        <div class="col-12"><label class="form-label">PDF File</label><input type="file" name="invoice_file" class="form-control" accept=".pdf"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Create Invoice</button></div>
            </div>
        </form>
    </div>
</div>
