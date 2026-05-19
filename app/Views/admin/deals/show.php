<!-- E:\call_center\app\Views\admin\deals\show.php -->
<?php
use App\Core\CSRF;
$deal = $deal ?? [];
?>
<div class="row g-4 mt-1">
    <!-- Deal Details -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bag-check me-1"></i> Deal Details</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><th class="text-muted fw-normal" width="40%">Business</th><td class="fw-semibold"><?= htmlspecialchars($deal['company_name'] ?? '') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Caller</th><td><?= htmlspecialchars($deal['caller_name'] ?? '') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Developer</th><td><?= htmlspecialchars($deal['developer_name'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Partner</th><td><?= htmlspecialchars($deal['partner_name'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Service</th><td><?= htmlspecialchars($deal['service_name'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Amount</th><td class="fw-bold fs-5 text-success">€<?= number_format($deal['amount'] ?? 0, 2) ?></td></tr>
                    <tr><th class="text-muted fw-normal">Status</th><td>
                        <span class="badge fs-6 <?= match($deal['status'] ?? ''){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>">
                            <?= ucfirst(str_replace('_',' ',$deal['status'] ?? '')) ?>
                        </span>
                    </td></tr>
                    <tr><th class="text-muted fw-normal">Contract</th><td>
                        <?= ($deal['contract_signed'] ?? 0) ? '<span class="badge bg-success">Signed</span>' : '<span class="badge bg-warning text-dark">Not Signed</span>' ?>
                    </td></tr>
                    <tr><th class="text-muted fw-normal">Created</th><td><?= isset($deal['created_at']) ? date('d M Y H:i', strtotime($deal['created_at'])) : '—' ?></td></tr>
                    <?php if(!empty($deal['approved_at'])): ?>
                    <tr><th class="text-muted fw-normal">Approved By</th><td><?= htmlspecialchars($deal['approved_by_name'] ?? '') ?> — <?= date('d M Y', strtotime($deal['approved_at'])) ?></td></tr>
                    <?php endif ?>
                </table>
                <?php if(!empty($deal['notes'])): ?><hr><p class="small text-muted"><?= nl2br(htmlspecialchars($deal['notes'])) ?></p><?php endif ?>
                <?php if(!empty($deal['proposal_file'])): ?><a href="<?= APP_URL ?>/assets/uploads/proposals/<?= htmlspecialchars($deal['proposal_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip me-1"></i>View Proposal</a><?php endif ?>
            </div>
        </div>

        <!-- Commission Summary -->
        <?php if(in_array($deal['status'] ?? '', ['approved','in_progress','completed'])): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-currency-euro me-1"></i> Commission Summary</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Caller (<?= COMMISSION_RATE ?>%)</td><td class="text-end fw-semibold">€<?= number_format($deal['amount'] * COMMISSION_RATE / 100, 2) ?></td></tr>
                    <?php if(!empty($deal['developer_id'])): ?>
                    <tr><td>Developer (20%)</td><td class="text-end fw-semibold">€<?= number_format($deal['amount'] * 0.20, 2) ?></td></tr>
                    <?php endif ?>
                    <?php if(!empty($deal['partner_id'])): ?>
                    <tr><td>Partner (20%)</td><td class="text-end fw-semibold">€<?= number_format($deal['amount'] * 0.20, 2) ?></td></tr>
                    <?php endif ?>
                </table>
            </div>
        </div>
        <?php endif ?>
    </div>

    <div class="col-lg-7">
        <!-- Actions -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-lightning me-1"></i> Actions</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php if(($deal['status']??'')==='pending'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/approve">
                        <?= CSRF::field() ?>
                        <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Approve Deal</button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/reject">
                        <?= CSRF::field() ?>
                        <button class="btn btn-danger"><i class="bi bi-x-circle me-1"></i>Reject Deal</button>
                    </form>
                    <?php endif ?>
                    <?php if(($deal['status']??'')==='approved'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/in-progress">
                        <?= CSRF::field() ?>
                        <button class="btn btn-primary"><i class="bi bi-arrow-right-circle me-1"></i>Mark In Progress</button>
                    </form>
                    <?php endif ?>
                    <?php if(($deal['status']??'')==='in_progress'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/completed">
                        <?= CSRF::field() ?>
                        <button class="btn btn-info text-white"><i class="bi bi-check2-all me-1"></i>Mark Completed</button>
                    </form>
                    <?php endif ?>

                    <!-- Sign Contract / Create Project -->
                    <?php if(!($deal['contract_signed'] ?? 0) && in_array($deal['status']??'',['approved','in_progress'])): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/sign-contract">
                        <?= CSRF::field() ?>
                        <button class="btn btn-outline-success"><i class="bi bi-pen me-1"></i>Sign Contract & Create Project</button>
                    </form>
                    <?php elseif($deal['contract_signed'] ?? 0): ?>
                    <a href="<?= APP_URL ?>/admin/projects" class="btn btn-outline-primary"><i class="bi bi-kanban me-1"></i>View Project</a>
                    <?php endif ?>

                    <a href="<?= APP_URL ?>/admin/deals" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
                </div>
            </div>
        </div>

        <!-- Assign Developer & Partner -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-badge me-1"></i> Assign Developer & Partner</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/assign-developer">
                            <?= CSRF::field() ?>
                            <label class="form-label small fw-semibold">Developer</label>
                            <?php
                            $devList = (new \App\Models\User())->developers();
                            ?>
                            <select name="developer_id" class="form-select form-select-sm mb-2">
                                <option value="">— Unassigned —</option>
                                <?php foreach ($devList as $dev): ?>
                                <option value="<?= $dev['id'] ?>" <?= ($deal['developer_id']??0)==$dev['id']?'selected':'' ?>><?= htmlspecialchars($dev['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                            <button class="btn btn-sm btn-outline-primary w-100">Assign Developer</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="POST" action="<?= APP_URL ?>/admin/deals/<?= $deal['id'] ?>/assign-partner">
                            <?= CSRF::field() ?>
                            <label class="form-label small fw-semibold">Partner</label>
                            <?php
                            $partnerList = (new \App\Models\User())->partners();
                            ?>
                            <select name="partner_id" class="form-select form-select-sm mb-2">
                                <option value="">— Unassigned —</option>
                                <?php foreach ($partnerList as $pt): ?>
                                <option value="<?= $pt['id'] ?>" <?= ($deal['partner_id']??0)==$pt['id']?'selected':'' ?>><?= htmlspecialchars($pt['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                            <button class="btn btn-sm btn-outline-warning w-100">Assign Partner</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contract Upload -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-file-earmark-pdf me-1"></i> Contract Documents</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/documents/contracts/<?= $deal['id'] ?>/upload" enctype="multipart/form-data" class="mb-3">
                    <?= CSRF::field() ?>
                    <div class="input-group input-group-sm">
                        <input type="file" name="contract_file" class="form-control" accept=".pdf,.doc,.docx" required>
                        <button class="btn btn-outline-primary"><i class="bi bi-upload me-1"></i>Upload</button>
                    </div>
                    <input type="text" name="notes" class="form-control form-control-sm mt-2" placeholder="Notes (optional)">
                </form>
                <?php
                $contracts = (new \App\Models\Contract())->forDeal($deal['id']);
                ?>
                <?php if(empty($contracts)): ?>
                <p class="text-muted small mb-0">No contracts uploaded.</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($contracts as $c): ?>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                            <span class="small"><?= htmlspecialchars($c['original_name']) ?></span>
                            <div class="text-muted" style="font-size:.7rem"><?= date('d M Y', strtotime($c['uploaded_at'])) ?> · <?= htmlspecialchars($c['uploader_name']) ?></div>
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

        <!-- Invoice Upload -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-1"></i> Invoices</span>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Invoice
                </button>
            </div>
            <div class="card-body p-0">
                <?php
                $invoices = (new \App\Models\Invoice())->forDeal($deal['id']);
                ?>
                <?php if(empty($invoices)): ?>
                <div class="text-center text-muted py-3 small">No invoices yet.</div>
                <?php else: ?>
                <table class="table table-hover mb-0 table-sm">
                    <thead class="table-light"><tr><th>No.</th><th>Amount</th><th>VAT</th><th>Total</th><th>Status</th><th>Due</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><?= htmlspecialchars($inv['invoice_no'] ?? '—') ?></td>
                            <td>€<?= number_format($inv['amount'],2) ?></td>
                            <td>€<?= number_format($inv['vat_amount'],2) ?></td>
                            <td class="fw-semibold">€<?= number_format($inv['total_amount'],2) ?></td>
                            <td><span class="badge <?= match($inv['status']){'draft'=>'bg-secondary','issued'=>'bg-info text-dark','sent'=>'bg-primary','paid'=>'bg-success',default=>'bg-secondary'} ?>"><?= ucfirst($inv['status']) ?></span></td>
                            <td><?= $inv['due_at'] ? date('d M Y', strtotime($inv['due_at'])) : '—' ?></td>
                            <td>
                                <?php if($inv['status']!=='paid'): ?>
                                <form method="POST" action="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/mark-paid" class="d-inline">
                                    <?= CSRF::field() ?><button class="btn btn-xs btn-outline-success" style="font-size:.7rem;padding:2px 6px">Paid</button>
                                </form>
                                <?php endif ?>
                                <?php if($inv['filename']): ?>
                                <a href="<?= APP_URL ?>/admin/documents/invoices/<?= $inv['id'] ?>/download" class="btn btn-outline-secondary" style="font-size:.7rem;padding:2px 6px"><i class="bi bi-download"></i></a>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Invoice Modal -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>/admin/documents/invoices/<?= $deal['id'] ?>/upload" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Invoice No.</label><input type="text" name="invoice_no" class="form-control" placeholder="INV-001"></div>
                        <div class="col-6"><label class="form-label">Amount (€) *</label><input type="number" name="amount" class="form-control" step="0.01" required></div>
                        <div class="col-6"><label class="form-label">VAT Rate (%)</label><input type="number" name="vat_rate" class="form-control" value="24" step="0.01"></div>
                        <div class="col-6"><label class="form-label">Status</label>
                            <select name="status" class="form-select"><option value="draft">Draft</option><option value="issued">Issued</option><option value="sent">Sent</option></select>
                        </div>
                        <div class="col-6"><label class="form-label">Issue Date</label><input type="date" name="issued_at" class="form-control"></div>
                        <div class="col-6"><label class="form-label">Due Date</label><input type="date" name="due_at" class="form-control"></div>
                        <div class="col-12"><label class="form-label">PDF File (optional)</label><input type="file" name="invoice_file" class="form-control" accept=".pdf"></div>
                        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Create Invoice</button></div>
            </div>
        </form>
    </div>
</div>
