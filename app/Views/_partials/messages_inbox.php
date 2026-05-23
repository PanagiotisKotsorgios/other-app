<?php use App\Core\CSRF; ?>

<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <ul class="nav nav-pills">
        <li class="nav-item"><a href="<?= APP_URL ?>/<?= $prefix ?>/messages" class="nav-link <?= ($tab??'inbox')==='inbox'?'active':'' ?>"><i class="bi bi-inbox me-1"></i>Εισερχόμενα</a></li>
        <li class="nav-item"><a href="<?= APP_URL ?>/<?= $prefix ?>/messages/sent" class="nav-link <?= ($tab??'')==='sent'?'active':'' ?>"><i class="bi bi-send me-1"></i>Απεσταλμένα</a></li>
    </ul>
    <a href="<?= APP_URL ?>/<?= $prefix ?>/messages/compose" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square me-1"></i>Νέο Μήνυμα</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0" id="msgTable">
            <thead class="table-light">
                <tr>
                    <th style="width:10px"></th>
                    <th><?= ($tab??'inbox')==='inbox' ? 'Από' : 'Προς' ?></th>
                    <th>Θέμα</th>
                    <th style="width:140px">Ημερομηνία</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $m): ?>
            <?php $unread = !$m['is_read'] && ($tab??'inbox')==='inbox'; ?>
            <tr class="msg-row <?= $unread ? 'fw-semibold' : '' ?>"
                data-id="<?= $m['id'] ?>"
                style="cursor:pointer"
                title="Κάντε κλικ για να ανοίξετε">
                <td>
                    <?php if($unread): ?>
                        <span class="badge rounded-pill bg-primary p-1">&nbsp;</span>
                    <?php endif ?>
                </td>
                <td><?= htmlspecialchars(($tab??'inbox')==='inbox' ? ($m['sender_name']??'') : ($m['receiver_name']??'')) ?></td>
                <td><?= htmlspecialchars($m['subject']) ?></td>
                <td class="text-muted small"><?= date('d M Y H:i', strtotime($m['created_at'])) ?></td>
            </tr>
            <?php endforeach ?>
            <?php if(empty($data)): ?>
            <tr><td colspan="4" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>
                Δεν υπάρχουν μηνύματα.
            </td></tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>
    <?php if(($last_page??1)>1): ?>
    <div class="card-footer bg-white"><?php include __DIR__.'/pagination.php' ?></div>
    <?php endif ?>
</div>

<!-- ── Message Modal ──────────────────────────────────────── -->
<div class="modal fade" id="msgModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header border-bottom pb-3">
                <div class="flex-grow-1 me-3">
                    <h5 class="modal-title fw-bold mb-1" id="msgModalSubject">—</h5>
                    <div class="d-flex gap-3 flex-wrap" style="font-size:.82rem;color:#6b7280">
                        <span><i class="bi bi-person me-1"></i><span id="msgModalFrom">—</span></span>
                        <span><i class="bi bi-calendar3 me-1"></i><span id="msgModalDate">—</span></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Κλείσιμο"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <!-- Thread messages -->
                <div id="msgThread"></div>

                <!-- Divider -->
                <hr class="my-3" id="msgReplyDivider">

                <!-- Reply form -->
                <div id="msgReplySection">
                    <div class="fw-semibold mb-2" style="font-size:.85rem;color:#374151">
                        <i class="bi bi-reply me-1 text-primary"></i>Απάντηση
                    </div>
                    <textarea id="msgReplyBody" class="form-control" rows="3"
                              placeholder="Γράψτε την απάντησή σας..."></textarea>
                    <div id="msgReplyError" class="text-danger small mt-1 d-none"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer justify-content-between border-top pt-3">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Κλείσιμο
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" id="msgMarkReadBtn">
                        <i class="bi bi-check2-all me-1"></i>Σήμανση Αναγνωσμένου
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="msgSendReplyBtn">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="msgReplySpin"></span>
                    <i class="bi bi-send me-1" id="msgReplyIcon"></i>Αποστολή Απάντησης
                </button>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const APP_URL    = window.APP_URL || '';
    const CSRF_TOKEN = <?= json_encode(CSRF::generate()) ?>;
    const modal      = new bootstrap.Modal(document.getElementById('msgModal'));

    let currentMsgId  = null;
    let replyToUserId = null;

    /* ── helpers ── */
    function fmt(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleDateString('el-GR', {day:'2-digit',month:'short',year:'numeric'})
             + ' ' + d.toLocaleTimeString('el-GR', {hour:'2-digit',minute:'2-digit'});
    }

    function renderThread(thread) {
        const container = document.getElementById('msgThread');
        container.innerHTML = '';
        thread.forEach(function(msg, i) {
            const isMine = msg.is_mine;
            const div = document.createElement('div');
            div.className = 'p-3 rounded mb-3 ' + (isMine
                ? 'border-start border-3 border-primary bg-light'
                : 'bg-white border');
            div.innerHTML =
                '<div class="d-flex justify-content-between mb-2">' +
                    '<span class="fw-semibold" style="font-size:.85rem">' +
                        '<i class="bi bi-person-circle me-1 text-muted"></i>' + esc(msg.sender_name) +
                        (isMine ? ' <span class="badge bg-secondary ms-1" style="font-size:.65rem">εσείς</span>' : '') +
                    '</span>' +
                    '<span class="text-muted" style="font-size:.78rem">' + fmt(msg.created_at) + '</span>' +
                '</div>' +
                '<div style="font-size:.92rem;line-height:1.65;white-space:pre-wrap">' + esc(msg.body) + '</div>';
            container.appendChild(div);
        });
    }

    function esc(str) {
        return String(str)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    /* ── open modal on row click ── */
    document.querySelectorAll('.msg-row').forEach(function(row) {
        row.addEventListener('click', function() {
            openMessage(parseInt(this.dataset.id));
        });
    });

    function openMessage(id) {
        currentMsgId = id;

        // reset
        document.getElementById('msgModalSubject').textContent = 'Φόρτωση…';
        document.getElementById('msgModalFrom').textContent    = '';
        document.getElementById('msgModalDate').textContent    = '';
        document.getElementById('msgThread').innerHTML         =
            '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm"></div></div>';
        document.getElementById('msgReplyBody').value          = '';
        document.getElementById('msgReplyError').classList.add('d-none');
        document.getElementById('msgMarkReadBtn').classList.remove('d-none');

        modal.show();

        fetch(APP_URL + '/api/messages/' + id, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            if (data.error) {
                document.getElementById('msgThread').innerHTML =
                    '<div class="alert alert-danger">' + esc(data.error) + '</div>';
                return;
            }
            const thread = data.thread;
            replyToUserId = data.reply_to_id;
            const first   = thread[0];

            document.getElementById('msgModalSubject').textContent = first.subject;
            document.getElementById('msgModalFrom').textContent    =
                first.is_mine ? ('Προς: ' + first.receiver_name) : ('Από: ' + first.sender_name);
            document.getElementById('msgModalDate').textContent    = fmt(first.created_at);

            renderThread(thread);

            // mark row as read visually
            const row = document.querySelector('.msg-row[data-id="' + id + '"]');
            if (row) {
                row.classList.remove('fw-semibold');
                const dot = row.querySelector('.badge');
                if (dot) dot.remove();
            }
        })
        .catch(function() {
            document.getElementById('msgThread').innerHTML =
                '<div class="alert alert-danger">Σφάλμα φόρτωσης μηνύματος.</div>';
        });
    }

    /* ── mark as read ── */
    document.getElementById('msgMarkReadBtn').addEventListener('click', function() {
        if (!currentMsgId) return;
        const btn = document.getElementById('msgMarkReadBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>…';

        fetch(APP_URL + '/api/messages/' + currentMsgId + '/read', {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: new URLSearchParams({'_csrf_token': CSRF_TOKEN})
        })
        .then(function(r) { return r.json(); })
        .then(function() {
            btn.innerHTML = '<i class="bi bi-check2-all me-1"></i>Αναγνωσμένο';
            btn.classList.replace('btn-outline-success', 'btn-success');
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-all me-1"></i>Σήμανση Αναγνωσμένου';
        });
    });

    /* ── send reply ── */
    document.getElementById('msgSendReplyBtn').addEventListener('click', function() {
        const body = document.getElementById('msgReplyBody').value.trim();
        const errEl = document.getElementById('msgReplyError');
        errEl.classList.add('d-none');

        if (!body) {
            errEl.textContent = 'Το μήνυμα δεν μπορεί να είναι κενό.';
            errEl.classList.remove('d-none');
            return;
        }

        const spin = document.getElementById('msgReplySpin');
        const icon = document.getElementById('msgReplyIcon');
        const btn  = document.getElementById('msgSendReplyBtn');
        spin.classList.remove('d-none');
        icon.classList.add('d-none');
        btn.disabled = true;

        const formData = new URLSearchParams({
            '_csrf_token': CSRF_TOKEN,
            'body':        body,
        });

        fetch(APP_URL + '/api/messages/' + currentMsgId + '/reply', {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: formData
        })
        .then(function(r) {
            if (!r.ok && r.status !== 422) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            spin.classList.add('d-none');
            icon.classList.remove('d-none');
            btn.disabled = false;

            if (data.error) {
                errEl.textContent = data.error;
                errEl.classList.remove('d-none');
                return;
            }

            // success — show confirmation, clear textarea
            document.getElementById('msgReplyBody').value = '';
            document.getElementById('msgReplySection').innerHTML =
                '<div class="alert alert-success py-2 mb-0">' +
                '<i class="bi bi-check-circle me-1"></i>Η απάντησή σας εστάλη επιτυχώς!</div>';

            // auto-close after 1.8s
            setTimeout(function() { modal.hide(); }, 1800);
        })
        .catch(function() {
            spin.classList.add('d-none');
            icon.classList.remove('d-none');
            btn.disabled = false;
            errEl.textContent = 'Σφάλμα αποστολής. Δοκιμάστε ξανά.';
            errEl.classList.remove('d-none');
        });
    });
});
</script>
