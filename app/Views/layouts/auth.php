<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Σύνδεση') ?> — SoftSystems</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* ── Left: Brand ─────────────────────────────────────────── */
        .brand-panel {
            width: 46%;
            min-height: 100vh;
            background: #0c0f17;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 3.5rem;
            border-right: 1px solid rgba(255,255,255,.06);
        }

        .brand-top { display: flex; flex-direction: column; }

        .brand-logo-wrap {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 3.5rem;
        }
        .brand-logo-wrap img {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            object-fit: cover;
        }
        .brand-name {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: -.3px;
            line-height: 1.1;
            color: #fff;
        }
        .brand-name span { color: #3b82f6; }

        .brand-headline {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.5px;
            line-height: 1.25;
            margin-bottom: 1rem;
        }
        .brand-headline em {
            font-style: normal;
            color: #3b82f6;
        }

        .brand-desc {
            font-size: .9rem;
            color: #64748b;
            line-height: 1.65;
            max-width: 340px;
        }

        .brand-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            margin-top: 3rem;
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 12px;
            overflow: hidden;
        }
        .stat-item {
            padding: 1.25rem 1.5rem;
            background: rgba(255,255,255,.025);
        }
        .stat-item:nth-child(2) { border-left: 1px solid rgba(255,255,255,.07); }
        .stat-item:nth-child(3) { border-top: 1px solid rgba(255,255,255,.07); }
        .stat-item:nth-child(4) {
            border-top: 1px solid rgba(255,255,255,.07);
            border-left: 1px solid rgba(255,255,255,.07);
        }
        .stat-num {
            font-size: 1.55rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.5px;
            line-height: 1;
            margin-bottom: .25rem;
        }
        .stat-num span { color: #3b82f6; }
        .stat-label {
            font-size: .75rem;
            color: #475569;
            font-weight: 500;
            letter-spacing: .2px;
        }

        /* ── Right: Form ─────────────────────────────────────────── */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            padding: 2.5rem 2rem;
        }

        .form-box {
            width: 100%;
            max-width: 380px;
            animation: fadeUp .38s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* form heading */
        .form-box h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.4px;
            margin-bottom: .35rem;
        }
        .auth-sub {
            font-size: .9rem;
            color: #374151;
            margin-bottom: 2rem;
        }

        /* error */
        .auth-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: .75rem 1rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            font-size: .875rem;
            color: #b91c1c;
        }
        .auth-alert i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

        /* fields */
        .field-wrap { margin-bottom: 1.2rem; }
        .field-label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: .45rem;
        }
        .input-wrap { position: relative; }
        .input-wrap .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: .95rem;
            pointer-events: none;
            z-index: 3;
            transition: color .15s;
        }
        .field-wrap input {
            width: 100%;
            padding: .82rem 1rem .82rem 2.6rem;
            background: #fff;
            border: 1.5px solid #9ca3af;
            border-radius: 8px;
            font-size: .95rem;
            font-family: inherit;
            color: #111827;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }
        .field-wrap input::placeholder { color: #6b7280; }
        .field-wrap input:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29,78,216,.1);
        }
        .input-wrap input:focus ~ .field-icon { color: #1d4ed8; }
        .field-wrap input.is-invalid { border-color: #dc2626; }
        .field-wrap input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
        .invalid-msg {
            font-size: .78rem;
            color: #dc2626;
            margin-top: .3rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* eye toggle */
        .eye-toggle {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: .25rem;
            display: flex;
            align-items: center;
            z-index: 4;
            transition: color .15s;
            font-size: 1rem;
        }
        .eye-toggle:hover { color: #1d4ed8; }
        .input-wrap.has-eye input { padding-right: 2.5rem; }

        /* button */
        .btn-signin {
            width: 100%;
            padding: .88rem;
            background: #1d4ed8;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: .95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            margin-top: 1.5rem;
            transition: background .15s;
            letter-spacing: .15px;
        }
        .btn-signin:hover  { background: #1e40af; }
        .btn-signin:active { background: #1e3a8a; }
        .btn-signin .spinner {
            display: none;
            width: 15px; height: 15px;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Mobile */
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .brand-panel {
                width: 100%;
                min-height: auto;
                padding: 1.75rem 1.5rem;
                border-right: none;
                border-bottom: 1px solid rgba(255,255,255,.07);
            }
            .brand-headline { font-size: 1.4rem; }
            .brand-desc, .brand-stats, .brand-footer { display: none; }
            .brand-top { margin-bottom: 0; }
            .brand-logo-wrap { margin-bottom: 0; }
            .form-panel { padding: 2rem 1.25rem 3rem; }
        }
    </style>
</head>
<body>

<!-- ── Left: Brand ──────────────────────────────────────────── -->
<div class="brand-panel">
    <div class="brand-top">
        <div class="brand-logo-wrap">
            <img src="<?= APP_URL ?>/assets/img/softsystems-logo.jpg" alt="SoftSystems">
            <div class="brand-name"><span>Soft</span>Systems</div>
        </div>

        <h1 class="brand-headline">Το δίκτυο συνεργατών σας,<br>οργανωμένο <em>τέλεια.</em></h1>
        <p class="brand-desc">Διαχείριση παραπομπών, συμφωνιών, προμηθειών και έργων — για κάθε συνεργάτη, προγραμματιστή και τηλεφωνητή σε ένα μέρος.</p>

        <div class="brand-stats">
            <div class="stat-item">
                <div class="stat-num">100<span>%</span></div>
                <div class="stat-label">Παρακολούθηση εσόδων</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">Real<span>-</span>time</div>
                <div class="stat-label">Αναφορές & αναλυτικά</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">4<span>+</span></div>
                <div class="stat-label">Ρόλοι χρηστών</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">A<span>–</span>D</div>
                <div class="stat-label">Κατηγορίες συνεργατών</div>
            </div>
        </div>
    </div>

</div>

<!-- ── Right: Form ──────────────────────────────────────────── -->
<div class="form-panel">
    <div class="form-box">
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
