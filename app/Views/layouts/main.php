<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Πίνακας Ελέγχου') ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <script>window.APP_URL = '<?= APP_URL ?>';</script>
</head>
<body class="sidebar-body">

<?php require_once __DIR__ . '/../_partials/gr_helpers.php'; ?>

<!-- Υπόβαθρο κινητού -->
<div id="sidebarBackdrop"></div>

<!-- Πλαϊνή μπάρα -->
<nav id="sidebar" class="sidebar">

    <!-- Λογότυπο -->
    <a class="sidebar-brand" href="<?= APP_URL ?>">
        <div class="sidebar-brand-icon"><i class="bi bi-headset"></i></div>
        <span class="sidebar-brand-text"><?= htmlspecialchars(APP_NAME) ?></span>
    </a>

    <?php
    use App\Core\Auth;
    $role = Auth::role();
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $isDev     = Auth::isDeveloper();
    $isPartner = Auth::isPartner();

    function navLink(string $href, string $icon, string $label, string $path): void {
        $segment = parse_url($href, PHP_URL_PATH);
        $active  = str_contains($path, $segment) ? 'active' : '';
        echo "<li><a href=\"{$href}\" class=\"nav-link {$active}\"><i class=\"bi bi-{$icon}\"></i><span>{$label}</span></a></li>";
    }
    ?>

    <ul class="sidebar-nav">

        <?php if ($role === 'admin'): ?>
        <li class="sidebar-section">Κύριο</li>
        <?php navLink(APP_URL.'/admin/dashboard','speedometer2','Πίνακας Ελέγχου',$path) ?>
        <?php navLink(APP_URL.'/admin/businesses','building','Επιχειρήσεις',$path) ?>
        <?php navLink(APP_URL.'/admin/import','file-earmark-arrow-up','Εισαγωγή Excel',$path) ?>

        <li class="sidebar-section">Άνθρωποι</li>
        <?php navLink(APP_URL.'/admin/callers','people','Τηλεφωνητές',$path) ?>
        <?php navLink(APP_URL.'/admin/developers','code-slash','Προγραμματιστές',$path) ?>
        <?php navLink(APP_URL.'/admin/partners','handshake','Συνεργάτες',$path) ?>
        <?php navLink(APP_URL.'/admin/categories','award','Κατηγορίες',$path) ?>

        <li class="sidebar-section">Οικονομικά</li>
        <?php navLink(APP_URL.'/admin/deals','bag-check','Συμφωνίες',$path) ?>
        <?php navLink(APP_URL.'/admin/projects','kanban','Έργα',$path) ?>
        <?php navLink(APP_URL.'/admin/commissions','currency-euro','Προμήθειες',$path) ?>
        <?php navLink(APP_URL.'/admin/financials','graph-up-arrow','Οικονομικές Αναφορές',$path) ?>

        <li class="sidebar-section">Άλλα</li>
        <li>
            <a href="<?= APP_URL ?>/admin/messages" class="nav-link <?= str_contains($path,'messages')?'active':'' ?>">
                <i class="bi bi-envelope"></i>
                <span>Μηνύματα</span>
                <?php if(($unread??0)>0): ?><span class="badge solid-danger ms-auto"><?= $unread ?></span><?php endif ?>
            </a>
        </li>

        <?php elseif ($role === 'developer'): ?>
        <li class="sidebar-section">Κύριο</li>
        <?php navLink(APP_URL.'/developer/dashboard','speedometer2','Πίνακας Ελέγχου',$path) ?>
        <?php navLink(APP_URL.'/developer/projects','kanban','Τα Έργα μου',$path) ?>
        <?php navLink(APP_URL.'/developer/commissions','currency-euro','Προμήθειες',$path) ?>
        <li>
            <a href="<?= APP_URL ?>/caller/messages" class="nav-link <?= str_contains($path,'messages')?'active':'' ?>">
                <i class="bi bi-envelope"></i>
                <span>Μηνύματα</span>
                <?php if(($unread??0)>0): ?><span class="badge solid-danger ms-auto"><?= $unread ?></span><?php endif ?>
            </a>
        </li>

        <?php elseif ($role === 'partner'): ?>
        <li class="sidebar-section">Κύριο</li>
        <?php navLink(APP_URL.'/partner/dashboard','speedometer2','Πίνακας Ελέγχου',$path) ?>

        <li class="sidebar-section">Παραπομπές</li>
        <?php navLink(APP_URL.'/partner/referrals?open=1','plus-circle','Νέα Παραπομπή',$path) ?>
        <?php navLink(APP_URL.'/partner/referrals','share','Όλες οι Παραπομπές',$path) ?>
        <?php navLink(APP_URL.'/partner/referrals?status=pending','hourglass-split','Εκκρεμείς',$path) ?>
        <?php navLink(APP_URL.'/partner/referrals?status=approved','check-circle','Εγκεκριμένες',$path) ?>

        <li class="sidebar-section">Οικονομικά</li>
        <?php navLink(APP_URL.'/partner/commissions','currency-euro','Προμήθειες',$path) ?>

        <?php if($isDev): ?>
        <li class="sidebar-section">Ανάπτυξη</li>
        <?php navLink(APP_URL.'/developer/projects','kanban','Τα Έργα μου',$path) ?>
        <?php navLink(APP_URL.'/developer/commissions','cash-coin','Προμήθειες Ανάπτυξης',$path) ?>
        <?php endif ?>

        <li class="sidebar-section">Άλλα</li>
        <li>
            <a href="<?= APP_URL ?>/caller/messages" class="nav-link <?= str_contains($path,'messages')?'active':'' ?>">
                <i class="bi bi-envelope"></i>
                <span>Μηνύματα</span>
                <?php if(($unread??0)>0): ?><span class="badge solid-danger ms-auto"><?= $unread ?></span><?php endif ?>
            </a>
        </li>

        <?php else: /* caller + multi-role */ ?>
        <li class="sidebar-section">Κύριο</li>
        <?php navLink(APP_URL.'/caller/dashboard','speedometer2','Πίνακας Ελέγχου',$path) ?>
        <?php navLink(APP_URL.'/caller/businesses','building','Οι Επιχειρήσεις μου',$path) ?>
        <?php navLink(APP_URL.'/caller/deals','bag-check','Οι Συμφωνίες μου',$path) ?>
        <?php navLink(APP_URL.'/caller/commissions','currency-euro','Προμήθειες',$path) ?>
        <?php if ($isDev): ?>
        <?php navLink(APP_URL.'/developer/projects','kanban','Έργα Dev',$path) ?>
        <?php endif ?>
        <?php if ($isPartner): ?>
        <?php navLink(APP_URL.'/partner/referrals','share','Παραπομπές',$path) ?>
        <?php endif ?>
        <li>
            <a href="<?= APP_URL ?>/caller/messages" class="nav-link <?= str_contains($path,'messages')?'active':'' ?>">
                <i class="bi bi-envelope"></i>
                <span>Μηνύματα</span>
                <?php if(($unread??0)>0): ?><span class="badge solid-danger ms-auto"><?= $unread ?></span><?php endif ?>
            </a>
        </li>
        <?php endif ?>

    </ul>

    <!-- Υποσέλιδο χρήστη -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr(Auth::name(),0,1)) ?></div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars(Auth::name()) ?></div>
                <div class="sidebar-user-role"><?= grRole($role) ?></div>
            </div>
            <a href="<?= APP_URL ?>/auth/logout" class="sidebar-logout" title="Αποσύνδεση">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>

</nav>

<!-- Κύριο περιεχόμενο -->
<div id="main-content" class="main-content">

    <!-- Μπάρα κορυφής -->
    <div class="topbar">
        <button class="topbar-toggle" id="sidebarToggle" aria-label="Εναλλαγή πλαϊνής μπάρας">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="topbar-title"><?= htmlspecialchars($title ?? '') ?></h1>
        <div class="topbar-actions">
            <?php
            $msgPrefix = match($role) {
                'admin'     => 'admin',
                default     => 'caller',
            };
            ?>
            <a href="<?= APP_URL ?>/<?= $msgPrefix ?>/messages" class="topbar-btn" title="Μηνύματα">
                <i class="bi bi-bell"></i>
                <?php if(($unread??0)>0): ?><span class="topbar-notif-dot" data-notify-badge></span><?php endif ?>
            </a>
            <a href="<?= APP_URL ?>/auth/profile" class="topbar-btn" title="Προφίλ">
                <i class="bi bi-person-circle"></i>
            </a>
        </div>
    </div>

    <!-- Μηνύματα flash -->
    <?php
    use App\Core\Session;
    $flashSuccess = Session::getFlash('success');
    $flashError   = Session::getFlash('error');
    $flashErrors  = Session::getFlash('errors', []);
    if ($flashSuccess || $flashError || $flashErrors):
    ?>
    <div class="flash-zone">
        <?php if ($flashSuccess): ?>
        <div class="flash-alert flash-success" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
            <button class="flash-close" aria-label="Κλείσιμο"><i class="bi bi-x-lg"></i></button>
        </div>
        <?php endif ?>
        <?php if ($flashError): ?>
        <div class="flash-alert flash-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($flashError) ?></span>
            <button class="flash-close" aria-label="Κλείσιμο"><i class="bi bi-x-lg"></i></button>
        </div>
        <?php endif ?>
        <?php if ($flashErrors): ?>
        <div class="flash-alert flash-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <ul class="mb-0 ps-3"><?php foreach ($flashErrors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach ?></ul>
            <button class="flash-close" aria-label="Κλείσιμο"><i class="bi bi-x-lg"></i></button>
        </div>
        <?php endif ?>
    </div>
    <?php endif ?>

    <!-- Περιεχόμενο σελίδας -->
    <div class="page-content">
        <?= $content ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
