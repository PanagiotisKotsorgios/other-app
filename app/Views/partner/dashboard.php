<?php
use App\Core\CSRF;
require_once __DIR__ . '/../_partials/gr_helpers.php';

$catColorMap = [
    'green'  => ['bg' => '#dcfce7', 'txt' => '#166534'],
    'blue'   => ['bg' => '#dbeafe', 'txt' => '#1d4ed8'],
    'orange' => ['bg' => '#fff7ed', 'txt' => '#c2410c'],
    'red'    => ['bg' => '#fee2e2', 'txt' => '#b91c1c'],
    'purple' => ['bg' => '#f3e8ff', 'txt' => '#7e22ce'],
    'teal'   => ['bg' => '#ccfbf1', 'txt' => '#0f766e'],
];
$catColor = $catColorMap[$user['cat_color'] ?? ''] ?? ['bg' => '#f1f5f9', 'txt' => '#475569'];
$partnerRate  = (float)($user['partner_rate']  ?? 20);
$developerRate = (float)($user['developer_rate'] ?? 20);
?>

<!-- Κλάση Συνεργάτη -->
<?php if (!empty($user['cat_name'])): ?>
<div class="alert border-0 d-flex align-items-center gap-3 mb-3 mt-1"
     style="background:<?= $catColor['bg'] ?>;color:<?= $catColor['txt'] ?>">
    <span class="fw-800 fs-4 border rounded px-2 py-1"
          style="border-color:<?= $catColor['txt'] ?>40 !important">
        <?= htmlspecialchars($user['cat_name']) ?>
    </span>
    <div>
        <div class="fw-700"><?= grCategory($user['cat_name']) ?> — <?= htmlspecialchars($user['cat_label']) ?></div>
        <div class="small">
            Προμήθεια παραπομπής: <strong><?= number_format($partnerRate, 1) ?>%</strong>
            <?php if ($isDeveloper): ?>
            &nbsp;·&nbsp; Προμήθεια ανάπτυξης: <strong><?= number_format($developerRate, 1) ?>%</strong>
            <?php endif ?>
        </div>
    </div>
</div>
<?php endif ?>

<!-- KPI -->
<div class="row g-3 mt-0">
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="bi bi-share"></i></div>
            <div class="kpi-value"><?= $stats['total_referrals'] ?? 0 ?></div>
            <div class="kpi-label">Συνολικές Παραπομπές</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-graph-up"></i></div>
            <div class="kpi-value">€<?= number_format($stats['revenue_generated'] ?? 0, 2) ?></div>
            <div class="kpi-label">Παραγόμενα Έσοδα</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-teal">
            <div class="kpi-icon"><i class="bi bi-currency-euro"></i></div>
            <div class="kpi-value">€<?= number_format($stats['commission_earned'] ?? 0, 2) ?></div>
            <div class="kpi-label">Κερδισμένη Προμήθεια</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-value">€<?= number_format($stats['commission_owed'] ?? 0, 2) ?></div>
            <div class="kpi-label">Οφειλόμενη Προμήθεια</div>
        </div>
    </div>
</div>

<!-- Ανάλυση προμηθειών (αν έχει και developer role) -->
<?php if($isDeveloper && (($stats['partner_commission_earned'] ?? 0) > 0 || ($stats['developer_commission_earned'] ?? 0) > 0)): ?>
<div class="row g-3 mt-0">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-5 fw-bold text-success">€<?= number_format($stats['partner_commission_earned'] ?? 0, 2) ?></div>
            <div class="small text-muted">Παραπομπές (<?= number_format($partnerRate, 1) ?>%)</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-5 fw-bold text-primary">€<?= number_format($stats['developer_commission_earned'] ?? 0, 2) ?></div>
            <div class="small text-muted">Ανάπτυξη (<?= number_format($developerRate, 1) ?>%)</div>
        </div>
    </div>
</div>
<?php endif ?>

<div class="row g-4 mt-0">
    <!-- Πρόσφατες Παραπομπές -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-share me-1"></i> Πρόσφατες Παραπομπές</span>
                <a href="<?= APP_URL ?>/partner/referrals" class="btn btn-sm btn-outline-primary">Όλες</a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($recentDeals)): ?>
                <div class="text-center text-muted py-4">Δεν υπάρχουν παραπομπές. Υποβάλετε την πρώτη σας παρακάτω!</div>
                <?php else: ?>
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Επιχείρηση</th><th>Ποσό</th><th>Κατάσταση</th><th>Εκτ. Προμήθεια</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentDeals as $deal): ?>
                        <tr>
                            <td><?= htmlspecialchars($deal['company_name']) ?></td>
                            <td>€<?= number_format($deal['amount'],2) ?></td>
                            <td><span class="badge <?= match($deal['status']){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>"><?= grStatus($deal['status']) ?></span></td>
                            <td class="text-success">€<?= number_format($deal['amount'] * $partnerRate / 100, 2) ?> (<?= number_format($partnerRate, 1) ?>%)</td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>

        <!-- Υποβολή Παραπομπής -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-plus-circle me-1 text-primary"></i> Υποβολή Παραπομπής</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/partner/referrals/submit">
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Επωνυμία Εταιρίας *</label>
                            <input type="text" name="company_name" class="form-control" required placeholder="Όνομα επιχείρησης">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Τηλέφωνο Επαφής</label>
                            <input type="text" name="phone" class="form-control" placeholder="+30...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Επαφής</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Πόλη</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Εκτιμώμενη Αξία Συμφωνίας (€) *</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Σημειώσεις</label>
                            <input type="text" name="notes" class="form-control" placeholder="Τυχόν πρόσθετες πληροφορίες...">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Υποβολή Παραπομπής</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Δεξί Panel -->
    <div class="col-lg-4">
        <!-- Πρόσφατες Προμήθειες -->
        <?php if(!empty($commData)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-currency-euro me-1 text-success"></i> Πρόσφατες Προμήθειες</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($commData as $c): ?>
                    <li class="list-group-item px-3">
                        <div class="d-flex justify-content-between">
                            <span class="small fw-semibold"><?= htmlspecialchars($c['company_name'] ?? '') ?></span>
                            <span class="fw-bold text-success">€<?= number_format($c['amount'],2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small"><?= grRoleType($c['role_type']) ?> · <?= $c['rate'] ?>%</span>
                            <span class="badge <?= $c['is_paid']?'bg-success':'bg-warning text-dark' ?>" style="font-size:.65rem"><?= $c['is_paid']?'Πληρωμένη':'Εκκρεμής' ?></span>
                        </div>
                    </li>
                    <?php endforeach ?>
                </ul>
                <div class="p-3 border-top"><a href="<?= APP_URL ?>/partner/commissions" class="btn btn-sm btn-outline-success w-100">Όλες οι Προμήθειες</a></div>
            </div>
        </div>
        <?php endif ?>

        <!-- Πώς λειτουργεί -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-question-circle me-1 text-info"></i> Πώς Λειτουργούν οι Παραπομπές</div>
            <div class="card-body">
                <ol class="small ps-3 mb-0">
                    <li class="mb-2">Υποβάλετε μια παραπομπή εταιρίας με τα στοιχεία της και την εκτιμώμενη αξία έργου.</li>
                    <li class="mb-2">Η ομάδα μας αξιολογεί την παραπομπή και επικοινωνεί με την εταιρία.</li>
                    <li class="mb-2">Αν εγκριθεί η συμφωνία, κερδίζετε <strong>προμήθεια <?= number_format($partnerRate, 1) ?>%</strong> (<?= grCategory($user['cat_name'] ?? '') ?>) επί της αξίας.</li>
                    <?php if($isDeveloper): ?>
                    <li class="mb-2">Ως <strong>Προγραμματιστής</strong> μπορείτε επίσης να αναλάβετε έργα και να κερδίσετε επιπλέον <strong><?= number_format($developerRate, 1) ?>% προμήθεια ανάπτυξης</strong>.</li>
                    <?php endif ?>
                    <li class="mb-2">Η προμήθεια καταβάλλεται μόλις τιμολογηθεί και εισπραχθεί το έργο.</li>
                    <li>Παρακολουθείτε τις παραπομπές και τις προμήθειες στον πίνακα ελέγχου.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
