/* ─── Sidebar toggle ────────────────────────────────────── */
document.getElementById('sidebarToggle')?.addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('main-content').classList.toggle('ms-0');
});

/* ─── Auto-dismiss alerts ───────────────────────────────── */
document.querySelectorAll('.alert').forEach(function (el) {
    setTimeout(function () {
        el.classList.remove('show');
        el.classList.add('fade');
        setTimeout(() => el.remove(), 500);
    }, 5000);
});

/* ─── DataTables init ───────────────────────────────────── */
document.querySelectorAll('table.datatable').forEach(function (table) {
    $(table).DataTable({
        paging: false,
        searching: false,
        info: false,
        order: [],
    });
});

/* ─── AJAX Interaction form (optional progressive enhancement) ─ */
const intForm = document.getElementById('interactionForm');
if (intForm) {
    intForm.addEventListener('submit', function (e) {
        // Allow normal form submit — AJAX enhancement is optional
    });
}

/* ─── Notification polling ──────────────────────────────── */
(function pollNotifications() {
    const badge = document.querySelector('[data-notify-badge]');
    if (!badge) return;

    function fetchCount() {
        fetch(window.APP_URL + '/api/messages/unread', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(() => {});
    }

    // Poll every 60 seconds
    setInterval(fetchCount, 60000);
}());
