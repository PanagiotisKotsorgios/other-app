<!-- E:\call_center\app\Views\partner\dashboard.php -->
<?php use App\Core\CSRF; ?>

<div class="row g-3 mt-1">
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="bi bi-share"></i></div>
            <div class="kpi-value"><?= $stats['total_referrals'] ?? 0 ?></div>
            <div class="kpi-label">Total Referrals</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-icon"><i class="bi bi-graph-up"></i></div>
            <div class="kpi-value">€<?= number_format($stats['revenue_generated'] ?? 0, 2) ?></div>
            <div class="kpi-label">Revenue Generated</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-teal">
            <div class="kpi-icon"><i class="bi bi-currency-euro"></i></div>
            <div class="kpi-value">€<?= number_format($stats['commission_earned'] ?? 0, 2) ?></div>
            <div class="kpi-label">Commission Earned</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card kpi-orange">
            <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="kpi-value">€<?= number_format($stats['commission_owed'] ?? 0, 2) ?></div>
            <div class="kpi-label">Commission Owed</div>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">
    <!-- Recent Referrals -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-share me-1"></i> Recent Referrals</span>
                <a href="<?= APP_URL ?>/partner/referrals" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($recentDeals)): ?>
                <div class="text-center text-muted py-4">No referrals yet. Submit your first referral below!</div>
                <?php else: ?>
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Business</th><th>Amount</th><th>Status</th><th>Commission</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentDeals as $deal): ?>
                        <tr>
                            <td><?= htmlspecialchars($deal['company_name']) ?></td>
                            <td>€<?= number_format($deal['amount'],2) ?></td>
                            <td><span class="badge <?= match($deal['status']){'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','in_progress'=>'bg-primary','completed'=>'bg-info text-dark',default=>'bg-secondary'} ?>"><?= ucfirst(str_replace('_',' ',$deal['status'])) ?></span></td>
                            <td class="text-success">€<?= number_format($deal['amount']*0.20,2) ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php endif ?>
            </div>
        </div>

        <!-- Submit Referral -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-plus-circle me-1 text-primary"></i> Submit a Referral</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/partner/referrals/submit">
                    <?= CSRF::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name *</label>
                            <input type="text" name="company_name" class="form-control" required placeholder="Business name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+30...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimated Deal Value (€) *</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Any additional info...">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Referral</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="col-lg-4">
        <!-- Commission Breakdown -->
        <?php if(!empty($commData)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-currency-euro me-1 text-success"></i> Recent Commissions</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($commData as $c): ?>
                    <li class="list-group-item px-3">
                        <div class="d-flex justify-content-between">
                            <span class="small fw-semibold"><?= htmlspecialchars($c['company_name'] ?? '') ?></span>
                            <span class="fw-bold text-success">€<?= number_format($c['amount'],2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small"><?= $c['rate'] ?>%</span>
                            <span class="badge <?= $c['is_paid']?'bg-success':'bg-warning text-dark' ?>" style="font-size:.65rem"><?= $c['is_paid']?'Paid':'Pending' ?></span>
                        </div>
                    </li>
                    <?php endforeach ?>
                </ul>
                <div class="p-3 border-top"><a href="<?= APP_URL ?>/partner/commissions" class="btn btn-sm btn-outline-success w-100">View All</a></div>
            </div>
        </div>
        <?php endif ?>

        <!-- How it works -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-question-circle me-1 text-info"></i> How Referrals Work</div>
            <div class="card-body">
                <ol class="small ps-3 mb-0">
                    <li class="mb-2">Submit a company referral with their details and estimated project value.</li>
                    <li class="mb-2">Our team reviews the referral and contacts the company.</li>
                    <li class="mb-2">If a deal is approved, you earn <strong>20% commission</strong> on the deal value.</li>
                    <li class="mb-2">Commission is paid once the project is invoiced and collected.</li>
                    <li>Track your referrals and commissions in the dashboard.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
