<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login') ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #1e3a5f 0%, #0d6efd 100%); min-height: 100vh; display:flex; align-items:center; }
        .auth-card { border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .auth-logo { font-size: 2rem; font-weight: 700; color: #0d6efd; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card auth-card border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="auth-logo"><i class="bi bi-headset"></i> CRM</div>
                        <p class="text-muted small mt-1"><?= APP_NAME ?></p>
                    </div>
                    <?= $content ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
