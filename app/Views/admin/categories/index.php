<?php use App\Core\CSRF;
$colorMap = [
    'green'  => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'],
    'blue'   => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
    'orange' => ['bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
    'red'    => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fecaca'],
    'purple' => ['bg' => '#f3e8ff', 'text' => '#7e22ce', 'border' => '#e9d5ff'],
    'teal'   => ['bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#99f6e4'],
];
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-award me-2"></i>Κατηγορίες Παραγωγικότητας</h1>
    <div class="page-header-actions">
        <a href="<?= APP_URL ?>/admin/categories/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Νέα Κατηγορία
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($categories as $cat):
        $c = $colorMap[$cat['color']] ?? $colorMap['blue'];
    ?>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100" style="border-left: 4px solid <?= $c['border'] ?> !important;">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="badge fs-4 fw-800 px-3 py-1"
                          style="background:<?= $c['bg'] ?>;color:<?= $c['text'] ?>;border:1px solid <?= $c['border'] ?>;">
                        <?= htmlspecialchars($cat['name']) ?>
                    </span>
                    <div class="d-flex gap-1">
                        <a href="<?= APP_URL ?>/admin/categories/<?= $cat['id'] ?>/edit" class="btn btn-xs btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="<?= APP_URL ?>/admin/categories/<?= $cat['id'] ?>/delete"
                              onsubmit="return confirm('Διαγραφή κατηγορίας «<?= htmlspecialchars($cat['name']) ?>»; Οι χρήστες θα χάσουν την ανάθεσή τους.')">
                            <?= CSRF::field() ?>
                            <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
                <div class="fw-600 mb-1"><?= htmlspecialchars($cat['label']) ?></div>
                <?php if($cat['description']): ?>
                <div class="text-xs text-muted mb-3"><?= htmlspecialchars($cat['description']) ?></div>
                <?php endif ?>
                <table class="table table-sm table-borderless mb-0" style="font-size:.82rem">
                    <tr>
                        <td class="text-muted ps-0">Ποσοστό Τηλεφωνητή</td>
                        <td class="fw-600 text-end pe-0"><?= number_format($cat['caller_rate'], 1) ?>%</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Ποσοστό Προγραμματιστή</td>
                        <td class="fw-600 text-end pe-0"><?= number_format($cat['developer_rate'], 1) ?>%</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Ποσοστό Συνεργάτη</td>
                        <td class="fw-600 text-end pe-0"><?= number_format($cat['partner_rate'], 1) ?>%</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach ?>
    <?php if(empty($categories)): ?>
    <div class="col-12">
        <div class="empty-state"><i class="bi bi-award"></i><p>Δεν υπάρχουν κατηγορίες ακόμα. Δημιουργήστε μία για να ξεκινήσετε.</p></div>
    </div>
    <?php endif ?>
</div>

<div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-info-circle me-1"></i>Πώς λειτουργούν οι κατηγορίες</div>
    <div class="card-body text-sm">
        <ul class="mb-0 ps-3" style="line-height:2">
            <li>Αναθέστε κατηγορία σε οποιονδήποτε τηλεφωνητή, προγραμματιστή ή συνεργάτη από τη σελίδα επεξεργασίας του.</li>
            <li>Κάθε κατηγορία αποθηκεύει ξεχωριστά ποσοστά προμήθειας για κάθε τύπο ρόλου.</li>
            <li>Όταν εγκρίνεται μια συμφωνία, χρησιμοποιείται αυτόματα το ποσοστό κατηγορίας του χρήστη (εφαρμόζεται το καθολικό ποσοστό αν δεν έχει οριστεί κατηγορία).</li>
            <li>Μπορείτε να δημιουργήσετε προσαρμοσμένες κατηγορίες με οποιοδήποτε όνομα (π.χ. "Χρυσός", "VIP", "Δοκιμαστικός").</li>
        </ul>
    </div>
</div>
