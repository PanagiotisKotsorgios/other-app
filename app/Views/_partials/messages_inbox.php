<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <ul class="nav nav-pills">
        <li class="nav-item"><a href="<?= APP_URL ?>/<?= $prefix ?>/messages" class="nav-link <?= ($tab??'inbox')==='inbox'?'active':'' ?>"><i class="bi bi-inbox me-1"></i>Inbox</a></li>
        <li class="nav-item"><a href="<?= APP_URL ?>/<?= $prefix ?>/messages/sent" class="nav-link <?= ($tab??'')==='sent'?'active':'' ?>"><i class="bi bi-send me-1"></i>Sent</a></li>
    </ul>
    <a href="<?= APP_URL ?>/<?= $prefix ?>/messages/compose" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square me-1"></i>New Message</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th></th>
                    <th><?= ($tab??'inbox')==='inbox' ? 'From' : 'To' ?></th>
                    <th>Subject</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $m): ?>
            <tr class="<?= !$m['is_read'] && ($tab??'inbox')==='inbox' ? 'fw-semibold' : '' ?>">
                <td>
                    <?php if(!$m['is_read'] && ($tab??'inbox')==='inbox'): ?>
                        <span class="badge rounded-pill bg-primary" style="width:8px;height:8px;padding:0">&nbsp;</span>
                    <?php endif ?>
                </td>
                <td><?= htmlspecialchars(($tab??'inbox')==='inbox' ? ($m['sender_name']??'') : ($m['receiver_name']??'')) ?></td>
                <td><a href="<?= APP_URL ?>/<?= $prefix ?>/messages/<?= $m['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($m['subject']) ?></a></td>
                <td class="text-muted small"><?= date('d M Y H:i', strtotime($m['created_at'])) ?></td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No messages.</td></tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if(($last_page??1)>1): ?><div class="card-footer bg-white"><?php include __DIR__.'/pagination.php' ?></div><?php endif ?>
</div>
