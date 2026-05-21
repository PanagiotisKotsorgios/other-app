/* ─── Sidebar toggle (mobile) ───────────────────────────── */
(function () {
    const sidebar   = document.getElementById('sidebar');
    const backdrop  = document.getElementById('sidebarBackdrop');
    const toggle    = document.getElementById('sidebarToggle');

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    toggle?.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    backdrop?.addEventListener('click', closeSidebar);

    // Close when a nav link is tapped on mobile
    sidebar?.querySelectorAll('.nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 1024) closeSidebar();
        });
    });
}());

/* ─── Flash alert dismiss ───────────────────────────────── */
document.querySelectorAll('.flash-close').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const alert = btn.closest('.flash-alert');
        alert.style.transition = 'opacity .25s, transform .25s';
        alert.style.opacity    = '0';
        alert.style.transform  = 'translateY(-6px)';
        setTimeout(() => alert.remove(), 260);
    });
});

/* Auto-dismiss flash alerts after 5 s */
document.querySelectorAll('.flash-alert').forEach(function (el) {
    setTimeout(function () {
        el.style.transition = 'opacity .4s, transform .4s';
        el.style.opacity    = '0';
        el.style.transform  = 'translateY(-6px)';
        setTimeout(() => el.remove(), 420);
    }, 5000);
});

/* ─── DataTables init ───────────────────────────────────── */
document.querySelectorAll('table.datatable').forEach(function (table) {
    $(table).DataTable({
        paging:    false,
        searching: false,
        info:      false,
        order:     [],
    });
});

/* ─── Notification polling ──────────────────────────────── */
(function pollNotifications() {
    const dot = document.querySelector('[data-notify-badge]');
    if (!dot) return;

    function fetchCount() {
        fetch(window.APP_URL + '/api/messages/unread', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                dot.style.display = data.count > 0 ? '' : 'none';
            })
            .catch(() => {});
    }

    setInterval(fetchCount, 60000);
}());
