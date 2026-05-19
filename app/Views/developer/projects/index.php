<!-- E:\call_center\app\Views\developer\projects\index.php -->
<!-- Stats row -->
<div class="row g-3 mt-1 mb-3">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-3 fw-bold text-primary"><?= $stats['total_projects'] ?? 0 ?></div><div class="small text-muted">Total</div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-3 fw-bold text-info"><?= $stats['in_progress'] ?? 0 ?></div><div class="small text-muted">In Progress</div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-3 fw-bold text-danger"><?= $stats['overdue'] ?? 0 ?></div><div class="small text-muted">Overdue</div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-3 fw-bold text-success"><?= $stats['completed'] ?? 0 ?></div><div class="small text-muted">Completed</div></div></div>
</div>

<!-- Filters -->
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <?php foreach (['awaiting_assignment','in_progress','testing','on_hold','completed'] as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="priority" class="form-select form-select-sm">
            <option value="">All Priorities</option>
            <?php foreach (['low','medium','high','urgent'] as $p): ?>
            <option value="<?= $p ?>" <?= ($filters['priority']??'')===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Filter</button></div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Project</th><th>Business</th><th>Status</th><th>Priority</th><th>Deadline</th><th>Budget</th><th>Progress</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($data as $proj): ?>
                    <?php
                    $isOverdue  = $proj['deadline'] && $proj['deadline'] < date('Y-m-d') && !in_array($proj['status'],['completed','on_hold']);
                    $phaseDone  = (int)($proj['phases_done'] ?? 0);
                    $phaseTotal = (int)($proj['phase_count'] ?? 0);
                    $pct        = $phaseTotal > 0 ? round($phaseDone/$phaseTotal*100) : 0;
                    ?>
                    <tr class="<?= $isOverdue?'table-danger':'' ?>">
                        <td class="fw-semibold"><?= htmlspecialchars($proj['title']) ?></td>
                        <td><?= htmlspecialchars($proj['company_name']) ?></td>
                        <td><span class="badge <?= match($proj['status']){'awaiting_assignment'=>'bg-secondary','in_progress'=>'bg-primary','testing'=>'bg-info text-dark','on_hold'=>'bg-warning text-dark','completed'=>'bg-success',default=>'bg-secondary'} ?>"><?= ucfirst(str_replace('_',' ',$proj['status'])) ?></span></td>
                        <td><span class="badge <?= match($proj['priority']){'low'=>'bg-light text-dark','medium'=>'bg-info text-dark','high'=>'bg-warning text-dark','urgent'=>'bg-danger',default=>'bg-secondary'} ?>"><?= ucfirst($proj['priority']) ?></span></td>
                        <td>
                            <?= $proj['deadline'] ? date('d M Y', strtotime($proj['deadline'])) : '—' ?>
                            <?php if($isOverdue): ?><br><span class="badge bg-danger">Overdue</span><?php endif ?>
                        </td>
                        <td>€<?= number_format($proj['budget'],2) ?></td>
                        <td style="min-width:90px">
                            <?php if($phaseTotal > 0): ?>
                            <div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div></div>
                            <small class="text-muted"><?= $pct ?>%</small>
                            <?php else: ?><small class="text-muted">No phases</small><?php endif ?>
                        </td>
                        <td><a href="<?= APP_URL ?>/developer/projects/<?= $proj['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                <?php endforeach ?>
                <?php if(empty($data)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No projects assigned to you.</td></tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(($last_page ?? 1) > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for($p=1;$p<=$last_page;$p++): ?>
    <li class="page-item <?= $p==($current_page??1)?'active':'' ?>"><a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $p ?></a></li>
    <?php endfor ?>
</ul></nav>
<?php endif ?>
