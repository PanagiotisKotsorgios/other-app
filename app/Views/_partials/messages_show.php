<?php use App\Core\{CSRF, Auth}; ?>
<div class="row justify-content-center mt-2">
    <div class="col-lg-8">
        <div class="mb-3">
            <a href="<?= APP_URL ?>/<?= $prefix ?>/messages" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Πίσω στα Εισερχόμενα</a>
        </div>

        <?php foreach ($thread as $msg): ?>
        <div class="card border-0 shadow-sm mb-3 <?= $msg['sender_id'] == Auth::id() ? 'ms-4 border-start border-primary border-3' : '' ?>">
            <div class="card-header bg-<?= $msg['sender_id']==Auth::id()?'light':'white' ?> d-flex justify-content-between">
                <span class="fw-semibold"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($msg['sender_name']) ?></span>
                <span class="text-muted small"><?= date('d M Y H:i', strtotime($msg['created_at'])) ?></span>
            </div>
            <div class="card-body">
                <?php if($msg['id']===$thread[0]['id']): ?><h6 class="fw-semibold mb-3"><?= htmlspecialchars($msg['subject']) ?></h6><?php endif ?>
                <div class="message-body"><?= nl2br(htmlspecialchars($msg['body'])) ?></div>
            </div>
        </div>
        <?php endforeach ?>

        <!-- Απάντηση -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-reply me-1"></i>Απάντηση</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/<?= $prefix ?>/messages/send">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="receiver_id" value="<?= $thread[0]['sender_id']==Auth::id() ? $thread[0]['receiver_id'] : $thread[0]['sender_id'] ?>">
                    <input type="hidden" name="subject" value="Απ: <?= htmlspecialchars($thread[0]['subject']) ?>">
                    <input type="hidden" name="parent_id" value="<?= $messageId ?>">
                    <div class="mb-3">
                        <textarea name="body" class="form-control" rows="4" placeholder="Γράψτε την απάντησή σας..." required></textarea>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Αποστολή Απάντησης</button>
                </form>
            </div>
        </div>
    </div>
</div>
