<?php require_once __DIR__ . '/../_partials/gr_helpers.php'; ?>
<!-- Καρτέλες Περιόδου -->
<ul class="nav nav-pills mt-2 mb-3">
    <li class="nav-item"><button class="nav-link active" onclick="showPeriod('daily')">Σήμερα</button></li>
    <li class="nav-item"><button class="nav-link" onclick="showPeriod('weekly')">Αυτή την Εβδομάδα</button></li>
    <li class="nav-item"><button class="nav-link" onclick="showPeriod('monthly')">Αυτόν τον Μήνα</button></li>
    <li class="nav-item"><button class="nav-link" onclick="showPeriod('all')">Συνολικά</button></li>
</ul>

<!-- KPI Κάρτες -->
<div id="stats-daily" class="stats-period">
    <div class="row g-3">
        <?php foreach ([['telephone','Κλήσεις',$daily['calls']??0,'blue'],['envelope','Emails',$daily['emails']??0,'teal'],['file-earmark-text','Προσφορές',$daily['offers']??0,'indigo'],['camera-video','Demos',$daily['demos']??0,'pink']] as [$icon,$label,$val,$color]): ?>
        <div class="col-6 col-xl-3"><div class="kpi-card kpi-<?= $color ?>"><div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div><div class="kpi-value"><?= $val ?></div><div class="kpi-label"><?= $label ?></div></div></div>
        <?php endforeach ?>
    </div>
</div>
<div id="stats-weekly" class="stats-period d-none">
    <div class="row g-3">
        <?php foreach ([['telephone','Κλήσεις',$weekly['calls']??0,'blue'],['envelope','Emails',$weekly['emails']??0,'teal'],['file-earmark-text','Προσφορές',$weekly['offers']??0,'indigo'],['camera-video','Demos',$weekly['demos']??0,'pink']] as [$icon,$label,$val,$color]): ?>
        <div class="col-6 col-xl-3"><div class="kpi-card kpi-<?= $color ?>"><div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div><div class="kpi-value"><?= $val ?></div><div class="kpi-label"><?= $label ?> (Εβδ.)</div></div></div>
        <?php endforeach ?>
    </div>
</div>
<div id="stats-monthly" class="stats-period d-none">
    <div class="row g-3">
        <?php foreach ([['telephone','Κλήσεις',$monthly['calls']??0,'blue'],['envelope','Emails',$monthly['emails']??0,'teal'],['file-earmark-text','Προσφορές',$monthly['offers']??0,'indigo'],['camera-video','Demos',$monthly['demos']??0,'pink']] as [$icon,$label,$val,$color]): ?>
        <div class="col-6 col-xl-3"><div class="kpi-card kpi-<?= $color ?>"><div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div><div class="kpi-value"><?= $val ?></div><div class="kpi-label"><?= $label ?> (Μήν.)</div></div></div>
        <?php endforeach ?>
    </div>
</div>
<div id="stats-all" class="stats-period d-none">
    <div class="row g-3">
        <?php foreach ([['building','Ανατεθειμένες',$stats['assigned_businesses']??0,'blue'],['telephone','Κλήσεις',$stats['total_calls']??0,'orange'],['bag-check','Εγκεκριμένες Συμφωνίες',$stats['deals_approved']??0,'green'],['currency-euro','Έσοδα','€'.number_format($stats['total_revenue']??0,2),'purple']] as [$icon,$label,$val,$color]): ?>
        <div class="col-6 col-xl-3"><div class="kpi-card kpi-<?= $color ?>"><div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div><div class="kpi-value"><?= $val ?></div><div class="kpi-label"><?= $label ?></div></div></div>
        <?php endforeach ?>
    </div>
</div>

<!-- Banner Προμήθειας -->
<div class="alert alert-info d-flex align-items-center mt-3">
    <i class="bi bi-cash-coin fs-4 me-3"></i>
    <div>
        <strong>Οφειλόμενη Προμήθεια:</strong> €<?= number_format($stats['commission_owed']??0,2) ?>
        <a href="<?= APP_URL ?>/caller/commissions" class="ms-3 btn btn-sm btn-info text-white">Προβολή Λεπτομερειών</a>
    </div>
</div>

<!-- Γραφήματα & Πίνακες -->
<div class="row g-4 mt-0">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Δραστηριότητα (Τελευταίες 30 Ημέρες)</div>
            <div class="card-body"><canvas id="actChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between"><span>Πρόσφατες Επιχειρήσεις</span><a href="<?= APP_URL ?>/caller/businesses" class="btn btn-xs btn-outline-primary">Όλες</a></div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light"><tr><th>Εταιρία</th><th>Πόλη</th><th>Κατάσταση</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $b): ?>
                    <tr>
                        <td><a href="<?= APP_URL ?>/caller/businesses/<?= $b['id'] ?>"><?= htmlspecialchars($b['company_name']) ?></a></td>
                        <td class="small text-muted"><?= htmlspecialchars($b['city']??'') ?></td>
                        <td><span class="badge bg-secondary small"><?= grStatus($b['status']) ?></span></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($recent)): ?><tr><td colspan="3" class="text-center text-muted py-3">Καμία ανάθεση.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between"><span>Πρόσφατες Συμφωνίες</span><a href="<?= APP_URL ?>/caller/deals" class="btn btn-xs btn-outline-primary">Όλες</a></div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light"><tr><th>Επιχείρηση</th><th>Ποσό</th><th>Κατάσταση</th><th>Ημερομηνία</th></tr></thead>
                    <tbody>
                    <?php foreach ($deals as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['company_name']) ?></td>
                        <td class="fw-semibold">€<?= number_format($d['amount'],2) ?></td>
                        <td><span class="badge bg-<?= match($d['status']){'pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger',default=>'secondary'} ?>"><?= grStatus($d['status']) ?></span></td>
                        <td class="small text-muted"><?= date('d M Y',strtotime($d['created_at'])) ?></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($deals)): ?><tr><td colspan="4" class="text-center text-muted py-3">Δεν υπάρχουν συμφωνίες.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function showPeriod(p) {
    document.querySelectorAll('.stats-period').forEach(el=>el.classList.add('d-none'));
    document.getElementById('stats-'+p).classList.remove('d-none');
    document.querySelectorAll('.nav-pills .nav-link').forEach(b=>b.classList.remove('active'));
    event.target.classList.add('active');
}

document.addEventListener('DOMContentLoaded',function(){
    const raw = <?= json_encode($actChart) ?>;
    const dates = [...new Set(raw.map(r=>r.date))].sort();
    const types = ['call','email','offer','demo'];
    const colors = {'call':'#0d6efd','email':'#0dcaf0','offer':'#198754','demo':'#6f42c1'};
    const labels = {'call':'Κλήση','email':'Email','offer':'Προσφορά','demo':'Demo'};
    const datasets = types.map(t=>({
        label: labels[t] || t,
        data: dates.map(d=>{ const r=raw.find(x=>x.date===d&&x.type===t); return r?parseInt(r.cnt):0; }),
        borderColor: colors[t], backgroundColor: colors[t]+'22', fill:true, tension:.4, pointRadius:3
    }));
    new Chart(document.getElementById('actChart'),{type:'line',data:{labels:dates,datasets},options:{plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
});
</script>
