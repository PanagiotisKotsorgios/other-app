<!DOCTYPE html>
<!-- E:\call_center\app\Views\layouts\main.php -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
</head>
<body class="sidebar-body">

<!-- Sidebar -->
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header d-flex align-items-center px-3 py-3">
        <i class="bi bi-headset fs-4 text-primary me-2"></i>
        <span class="fw-bold fs-5"><?= APP_NAME ?></span>
    </div>

    <?php
    use App\Core\Auth;
    $role = Auth::role();
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Determine extra roles from session (loaded by Auth::hasRole)
    $isDev     = Auth::isDeveloper();
    $isPartner = Auth::isPartner();

    function navLink(string $href, string $icon, string $label, string $path): void {
        $segment = parse_url($href, PHP_URL_PATH);
        $active  = str_contains($path, $segment) ? 'active' : '';
        echo "<a href=\"{$href}\" class=\"nav-link {$active}\"><i class=\"bi bi-{$icon} me-2\"></i>{$label}</a>";
    }
    ?>

    <ul class="nav flex-column px-2 mt-2">
        <?php if ($role === 'admin'): ?>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/dashboard','speedometer2','Dashboard',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/businesses','building','Businesses',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/import','file-earmark-arrow-up','Import Excel',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/callers','people','Callers',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/developers','code-slash','Developers',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/partners','handshake','Partners',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/deals','bag-check','Deals',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/projects','kanban','Projects',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/commissions','currency-euro','Commissions',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/admin/financials','graph-up-arrow','Financials',$path) ?></li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/admin/messages" class="nav-link <?= str_contains($path,'messages')?'active':'' ?>">
                <i class="bi bi-envelope me-2"></i>Messages
                <?php if(($unread??0)>0): ?><span class="badge bg-danger ms-1"><?= $unread ?></span><?php endif ?>
            </a>
        </li>

        <?php elseif ($role === 'developer'): ?>
        <li class="nav-item"><?php navLink(APP_URL.'/developer/dashboard','speedometer2','Dashboard',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/developer/projects','kanban','My Projects',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/developer/commissions','currency-euro','Commissions',$path) ?></li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/caller/messages" class="nav-link <?= str_contains($path,'messages')?'active':'' ?>">
                <i class="bi bi-envelope me-2"></i>Messages
                <?php if(($unread??0)>0): ?><span class="badge bg-danger ms-1"><?= $unread ?></span><?php endif ?>
            </a>
        </li>

        <?php elseif ($role === 'partner'): ?>
        <li class="nav-item"><?php navLink(APP_URL.'/partner/dashboard','speedometer2','Dashboard',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/partner/referrals','share','My Referrals',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/partner/commissions','currency-euro','Commissions',$path) ?></li>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/caller/messages" class="nav-link <?= str_contains($path,'messages')?'active':'' ?>">
                <i class="bi bi-envelope me-2"></i>Messages
                <?php if(($unread??0)>0): ?><span class="badge bg-danger ms-1"><?= $unread ?></span><?php endif ?>
            </a>
        </li>

        <?php else: /* caller + multi-role users */ ?>
        <li class="nav-item"><?php navLink(APP_URL.'/caller/dashboard','speedometer2','Dashboard',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/caller/businesses','building','My Businesses',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/caller/deals','bag-check','My Deals',$path) ?></li>
        <li class="nav-item"><?php navLink(APP_URL.'/caller/commissions','currency-euro','Commissions',$path) ?></li>
        <?php if ($isDev): ?>
        <li class="nav-item"><?php navLink(APP_URL.'/developer/projects','kanban','Dev Projects',$path) ?></li>
        <?php endif ?>
        <?php if ($isPartner): ?>
        <li class="nav-item"><?php navLink(APP_URL.'/partner/referrals','share','Referrals',$path) ?></li>
        <?php endif ?>
        <li class="nav-item">
            <a href="<?= APP_URL ?>/caller/messages" class="nav-link <?= str_contains($path,'messages')?'active':'' ?>">
                <i class="bi bi-envelope me-2"></i>Messages
                <?php if(($unread??0)>0): ?><span class="badge bg-danger ms-1"><?= $unread ?></span><?php endif ?>
            </a>
        </li>
        <?php endif ?>
    </ul>

    <div class="sidebar-footer px-3 py-3 mt-auto">
        <div class="d-flex align-items-center">
            <div class="avatar-circle me-2"><?= strtoupper(substr(Auth::name(),0,1)) ?></div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-semibold text-truncate small"><?= htmlspecialchars(Auth::name()) ?></div>
                <div class="text-muted" style="font-size:.7rem"><?= ucfirst($role) ?></div>
            </div>
            <a href="<?= APP_URL ?>/auth/logout" class="btn btn-sm btn-outline-secondary" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>

<!-- Main -->
<div id="main-content" class="main-content">
    <!-- Top bar -->
    <div class="topbar d-flex align-items-center px-4">
        <button class="btn btn-sm btn-light me-3" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
        <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($title ?? '') ?></h6>
        <div class="ms-auto d-flex align-items-center gap-2">
            <?php
            $msgPrefix = match($role) {
                'admin'     => 'admin',
                'developer' => 'caller',
                'partner'   => 'caller',
                default     => 'caller',
            };
            ?>
            <a href="<?= APP_URL ?>/<?= $msgPrefix ?>/messages" class="btn btn-sm btn-light position-relative">
                <i class="bi bi-bell"></i>
                <?php if(($unread??0)>0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem"><?= $unread ?></span><?php endif ?>
            </a>
            <a href="<?= APP_URL ?>/auth/profile" class="btn btn-sm btn-light"><i class="bi bi-person-circle"></i></a>
        </div>
    </div>

    <!-- Flash messages -->
    <div class="px-4 pt-3">
        <?php
        use App\Core\Session;
        $flashSuccess = Session::getFlash('success');
        $flashError   = Session::getFlash('error');
        $flashErrors  = Session::getFlash('errors', []);
        ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
        <?php if ($flashErrors): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0"><?php foreach ($flashErrors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach ?></ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
    </div>

    <!-- Page content -->
    <div class="page-content px-4 pb-5">
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
