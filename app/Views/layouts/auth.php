<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login') ?> — <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            background: #f0f4f8;
        }

        /* ── Left brand panel ─────────────────────────────── */
        .auth-brand {
            width: 45%;
            min-height: 100vh;
            background: linear-gradient(155deg, #0f172a 0%, #1e3a5f 45%, #1d4ed8 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 3rem 3.5rem;
            position: relative;
            overflow: hidden;
        }

        /* decorative circles */
        .auth-brand::before {
            content: '';
            position: absolute;
            width: 420px; height: 420px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            top: -120px; right: -120px;
        }
        .auth-brand::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            bottom: -80px; left: -80px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 3rem;
            position: relative;
            z-index: 1;
        }
        .brand-logo-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            box-shadow: 0 8px 20px rgba(59,130,246,.4);
        }
        .brand-logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.3px;
        }
        .brand-logo-text span { color: #93c5fd; }

        .brand-headline {
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -.5px;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        .brand-headline em {
            font-style: normal;
            color: #93c5fd;
        }

        .brand-sub {
            font-size: .95rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 1;
        }

        .brand-features {
            list-style: none;
            padding: 0; margin: 0;
            position: relative;
            z-index: 1;
        }
        .brand-features li {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: #cbd5e1;
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        .brand-features li .feat-icon {
            width: 32px; height: 32px;
            background: rgba(255,255,255,.08);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #60a5fa;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .brand-footer {
            margin-top: auto;
            padding-top: 3rem;
            font-size: .75rem;
            color: #475569;
            position: relative;
            z-index: 1;
        }

        /* ── Right form panel ─────────────────────────────── */
        .auth-form-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 2.5rem;
        }

        .auth-form-box {
            width: 100%;
            max-width: 420px;
            animation: fadeUp .45s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .auth-form-box h2 {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.4px;
            margin-bottom: .35rem;
        }
        .auth-form-box .auth-sub {
            font-size: .875rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        /* Floating label inputs */
        .field-wrap {
            position: relative;
            margin-bottom: 1.25rem;
        }
        .field-wrap .field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
            pointer-events: none;
            z-index: 3;
            transition: color .2s;
        }
        .field-wrap input {
            width: 100%;
            padding: .875rem 1rem .875rem 2.75rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: .95rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .field-wrap input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        }
        .field-wrap input:focus ~ .field-icon,
        .field-wrap input:not(:placeholder-shown) ~ .field-icon {
            color: #3b82f6;
        }
        .field-wrap input.is-invalid {
            border-color: #ef4444;
        }
        .field-wrap input.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239,68,68,.12);
        }
        .field-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #475569;
            letter-spacing: .3px;
            text-transform: uppercase;
            margin-bottom: .4rem;
        }
        .invalid-msg {
            font-size: .78rem;
            color: #ef4444;
            margin-top: .3rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* Eye toggle */
        .eye-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: .25rem;
            display: flex;
            align-items: center;
            z-index: 4;
            transition: color .2s;
            font-size: 1.05rem;
        }
        .eye-toggle:hover { color: #3b82f6; }

        /* Password field has padding-right for eye button */
        .field-wrap.has-eye input { padding-right: 2.75rem; }

        /* Submit button */
        .btn-signin {
            width: 100%;
            padding: .9rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: .95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: 1.5rem;
            transition: transform .15s, box-shadow .15s, background .2s;
            box-shadow: 0 4px 14px rgba(37,99,235,.35);
        }
        .btn-signin:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            box-shadow: 0 6px 20px rgba(37,99,235,.45);
            transform: translateY(-1px);
        }
        .btn-signin:active { transform: translateY(0); }
        .btn-signin .spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Error alert */
        .auth-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: .75rem 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            font-size: .875rem;
            color: #b91c1c;
        }
        .auth-alert i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

        /* Divider */
        .auth-divider {
            text-align: center;
            position: relative;
            margin: 1.5rem 0 .5rem;
        }
        .auth-divider::before {
            content: '';
            position: absolute;
            top: 50%; left: 0; right: 0;
            height: 1px;
            background: #e2e8f0;
        }
        .auth-divider span {
            background: #f0f4f8;
            padding: 0 .75rem;
            font-size: .75rem;
            color: #94a3b8;
            position: relative;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 500;
        }

        /* Responsive: stack on mobile */
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .auth-brand {
                width: 100%;
                min-height: auto;
                padding: 2rem 1.5rem;
            }
            .brand-headline { font-size: 1.6rem; }
            .brand-features { display: none; }
            .brand-footer { display: none; }
            .auth-form-wrap { padding: 2rem 1.25rem; }
        }
    </style>
</head>
<body>

<!-- Left: brand panel -->
<div class="auth-brand">
    <div class="brand-logo">
        <div class="brand-logo-icon"><i class="bi bi-headset"></i></div>
        <div class="brand-logo-text"><?= htmlspecialchars(APP_NAME) ?></div>
    </div>

    <h1 class="brand-headline">Manage your<br>sales pipeline<br>with <em>precision.</em></h1>
    <p class="brand-sub">A complete CRM built for call center teams — track every call, deal, and commission in one place.</p>

    <ul class="brand-features">
        <li>
            <span class="feat-icon"><i class="bi bi-telephone-fill"></i></span>
            Full interaction history per business
        </li>
        <li>
            <span class="feat-icon"><i class="bi bi-graph-up-arrow"></i></span>
            Real-time deal & revenue analytics
        </li>
        <li>
            <span class="feat-icon"><i class="bi bi-people-fill"></i></span>
            Multi-role: callers, developers, partners
        </li>
        <li>
            <span class="feat-icon"><i class="bi bi-shield-check"></i></span>
            Secure, role-based access control
        </li>
    </ul>

    <div class="brand-footer">© <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>. All rights reserved.</div>
</div>

<!-- Right: form panel -->
<div class="auth-form-wrap">
    <div class="auth-form-box">
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
