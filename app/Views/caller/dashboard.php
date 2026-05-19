<!-- Period Tabs -->
<ul class="nav nav-pills mt-2 mb-3">
    <li class="nav-item"><button class="nav-link active" onclick="showPeriod('daily')">Today</button></li>
    <li class="nav-item"><button class="nav-link" onclick="showPeriod('weekly')">This Week</button></li>
    <li class="nav-item"><button class="nav-link" onclick="showPeriod('monthly')">This Month</button></li>
    <li class="nav-item"><button class="nav-link" onclick="showPeriod('all')">All Time</button></li>
</ul>

<!-- KPI Cards -->
<div id="stats-daily" class="stats-period">
    <div class="row g-3">
        <?php foreach ([['telephone','Calls',$daily['calls']??0,'blue'],['envelope','Emails',$daily['emails']??0,'teal'],['file-earmark-text','Offers',$daily['offers']??0,'indigo'],['camera-video','Demos',$daily['demos']??0,'pink']] as [$icon,$label,$val,$color]): ?>
        <div class="col-6 col-xl-3"><div class="kpi-card kpi-<?= $color ?>"><div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div><div class="kpi-value"><?= $val ?></div><div class="kpi-label"><?= $label ?></div></div></div>
        <?php endforeach ?>
    </div>
</div>
<div id="stats-weekly" class="stats-period d-none">
    <div class="row g-3">
        <?php foreach ([['telephone','Calls',$weekly['calls']??0,'blue'],['envelope','Emails',$weekly['emails']??0,'teal'],['file-earmark-text','Offers',$weekly['offers']??0,'indigo'],['camera-video','Demos',$weekly['demos']??0,'pink']] as [$icon,$label,$val,$color]): ?>
        <div class="col-6 col-xl-3"><div class="kpi-card kpi-<?= $color ?>"><div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div><div class="kpi-value"><?= $val ?></div><div class="kpi-label"><?= $label ?> (Week)</div></div></div>
        <?php endforeach ?>
    </div>
</div>
<div id="stats-monthly" class="stats-period d-none">
    <div class="row g-3">
        <?php foreach ([['telephone','Calls',$monthly['calls']??0,'blue'],['envelope','Emails',$monthly['emails']??0,'teal'],['file-earmark-text','Offers',$monthly['offers']??0,'indigo'],['camera-video','Demos',$monthly['demos']??0,'pink']] as [$icon,$label,$val,$color]): ?>
        <div class="col-6 col-xl-3"><div class="kpi-card kpi-<?= $color ?>"><div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div><div class="kpi-value"><?= $val ?></div><div class="kpi-label"><?= $label ?> (Month)</div></div></div>
        <?php endforeach ?>
    </div>
</div>
<div id="stats-all" class="stats-period d-none">
    <div class="row g-3">
        <?php foreach ([['building','Assigned',$stats['assigned_businesses']??0,'blue'],['telephone','Calls',$stats['total_calls']??0,'orange'],['bag-check','Deals Approved',$stats['deals_approved']??0,'green'],['currency-euro','Revenue','€'.number_format($stats['total_revenue']??0,2),'purple']] as [$icon,$label,$val,$color]): ?>
        <div class="col-6 col-xl-3"><div class="kpi-card kpi-<?= $color ?>"><div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div><div class="kpi-value"><?= $val ?></div><div class="kpi-label"><?= $label ?></div></div></div>
        <?php endforeach ?>
    </div>
</div>

<!-- Commission Banner -->
<div class="alert alert-info d-flex align-items-center mt-3">
    <i class="bi bi-cash-coin fs-4 me-3"></i>
    <div>
        <strong>Commission Owed:</strong> €<?= number_format($stats['commission_owed']??0,2) ?>
        <a href="<?= APP_URL ?>/caller/commissions" class="ms-3 btn btn-sm btn-info text-white">View Details</a>
    </div>
</div>

<!-- Charts + Tables -->
<div class="row g-4 mt-0">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Activity (Last 30 Days)</div>
            <div class="card-body"><canvas id="actChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between"><span>Recent Businesses</span><a href="<?= APP_URL ?>/caller/businesses" class="btn btn-xs btn-outline-primary">View All</a></div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light"><tr><th>Company</th><th>City</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $b): ?>
                    <tr>
                        <td><a href="<?= APP_URL ?>/caller/businesses/<?= $b['id'] ?>"><?= htmlspecialchars($b['company_name']) ?></a></td>
                        <td class="small text-muted"><?= htmlspecialchars($b['city']??'') ?></td>
                        <td><span class="badge bg-secondary small"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($recent)): ?><tr><td colspan="3" class="text-center text-muted py-3">None assigned.</td></tr><?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between"><span>Recent Deals</span><a href="<?= APP_URL ?>/caller/deals" class="btn btn-xs btn-outline-primary">View All</a></div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light"><tr><th>Business</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($deals as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['company_name']) ?></td>
                        <td class="fw-semibold">€<?= number_format($d['amount'],2) ?></td>
                        <td><span class="badge bg-<?= match($d['status']){'pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger',default=>'secondary'} ?>"><?= ucfirst(str_replace('_',' ',$d['status'])) ?></span></td>
                        <td class="small text-muted"><?= date('d M Y',strtotime($d['created_at'])) ?></td>
                    </tr>
                    <?php endforeach ?>
                    <?php if(empty($deals)): ?><tr><td colspan="4" class="text-center text-muted py-3">No deals yet.</td></tr><?php endif ?>
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
    const datasets = types.map(t=>({
        label: t.charAt(0).toUpperCase()+t.slice(1),
        data: dates.map(d=>{ const r=raw.find(x=>x.date===d&&x.type===t); return r?parseInt(r.cnt):0; }),
        borderColor: colors[t], backgroundColor: colors[t]+'22', fill:true, tension:.4, pointRadius:3
    }));
    new Chart(document.getElementById('actChart'),{type:'line',data:{labels:dates,datasets},options:{plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
});
</script>
