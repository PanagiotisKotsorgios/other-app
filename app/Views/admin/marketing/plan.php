<?php /* Marketing & Growth Plan — Admin View */ ?>

<style>
.plan-hero{background:linear-gradient(135deg,#1a56db 0%,#0e3a8c 100%);color:#fff;border-radius:12px;padding:2rem 2.5rem;margin-bottom:1.5rem}
.plan-hero h1{font-size:1.7rem;font-weight:700;margin-bottom:.3rem}
.plan-hero p{opacity:.85;margin:0;font-size:.95rem}
.section-anchor{scroll-margin-top:70px}
.toc-card{position:sticky;top:70px}
.toc-card .list-group-item{font-size:.82rem;padding:.4rem .9rem;border:none;border-left:3px solid transparent}
.toc-card .list-group-item:hover,.toc-card .list-group-item.active{border-left-color:#1a56db;background:#f0f4ff;color:#1a56db}
.plan-section{background:#fff;border-radius:10px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:1.5rem 1.75rem;margin-bottom:1.25rem}
.plan-section h2{font-size:1.15rem;font-weight:700;color:#1a56db;border-bottom:2px solid #e8eef8;padding-bottom:.5rem;margin-bottom:1rem}
.plan-section h3{font-size:.97rem;font-weight:600;color:#374151;margin-top:1.1rem;margin-bottom:.5rem}
.kpi-box{background:#f8faff;border:1px solid #dde6ff;border-radius:8px;padding:.7rem 1rem;text-align:center}
.kpi-box .val{font-size:1.4rem;font-weight:700;color:#1a56db}
.kpi-box .lbl{font-size:.75rem;color:#6b7280;margin-top:1px}
.phase-badge{display:inline-block;padding:.2rem .7rem;border-radius:20px;font-size:.72rem;font-weight:600;letter-spacing:.3px}
.phase-1{background:#dbeafe;color:#1d4ed8}
.phase-2{background:#d1fae5;color:#065f46}
.phase-3{background:#fef3c7;color:#92400e}
.phase-4{background:#f3e8ff;color:#6b21a8}
.channel-card{border-left:4px solid #1a56db;border-radius:6px;background:#f8faff;padding:.75rem 1rem;margin-bottom:.6rem}
.channel-card.green{border-left-color:#198754}
.channel-card.orange{border-left-color:#fd7e14}
.channel-card.purple{border-left-color:#6f42c1}
.channel-card.teal{border-left-color:#0dcaf0}
.script-box{background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:1rem 1.2rem;font-size:.83rem;line-height:1.7}
.script-box .caller{color:#1a56db;font-weight:600}
.script-box .prospect{color:#374151}
.objection{background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;padding:.6rem 1rem;font-size:.82rem;margin-bottom:.5rem}
.objection strong{color:#c2410c}
.objection .answer{color:#374151;margin-top:.2rem}
table.plan-table{width:100%;border-collapse:collapse;font-size:.83rem}
table.plan-table th{background:#1a56db;color:#fff;padding:6px 10px;text-align:left}
table.plan-table td{padding:6px 10px;border-bottom:1px solid #e5e7eb;vertical-align:top}
table.plan-table tr:nth-child(even) td{background:#f8fafc}
.timeline-row{display:flex;gap:1rem;margin-bottom:.8rem;align-items:flex-start}
.timeline-dot{width:12px;height:12px;border-radius:50%;background:#1a56db;margin-top:4px;flex-shrink:0}
.timeline-dot.green{background:#198754}
.timeline-dot.orange{background:#fd7e14}
.timeline-dot.purple{background:#6f42c1}
.timeline-line{width:2px;background:#e5e7eb;flex-shrink:0;margin:0 5px}
</style>

<!-- Hero -->
<div class="plan-hero mt-2">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1><i class="bi bi-rocket-takeoff me-2"></i>Σχέδιο Marketing & Απόκτησης Πελατών</h1>
            <p>Αναλυτική στρατηγική για την ανάπτυξη της εταιρίας — από την Αιτωλοακαρνανία στην υπόλοιπη Ελλάδα</p>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="fs-5 fw-bold opacity-90">SoftSystems</div>
            <div class="small opacity-70">Web Development &amp; Digital Solutions</div>
        </div>
    </div>
</div>

<div class="row g-3">
<!-- TOC -->
<div class="col-lg-2 d-none d-lg-block">
    <div class="toc-card">
        <div class="list-group">
            <a href="#s1" class="list-group-item list-group-item-action">1. Ανάλυση Αγοράς</a>
            <a href="#s2" class="list-group-item list-group-item-action">2. Υπηρεσίες &amp; Τιμολόγηση</a>
            <a href="#s3" class="list-group-item list-group-item-action">3. Εύρεση Leads</a>
            <a href="#s4" class="list-group-item list-group-item-action">4. Διαδικασία Πωλήσεων</a>
            <a href="#s5" class="list-group-item list-group-item-action">5. Script Κλήσεων</a>
            <a href="#s6" class="list-group-item list-group-item-action">6. Αντιρρήσεις</a>
            <a href="#s7" class="list-group-item list-group-item-action">7. KPIs &amp; Στόχοι</a>
            <a href="#s8" class="list-group-item list-group-item-action">8. Digital Marketing</a>
            <a href="#s9" class="list-group-item list-group-item-action">9. Συνεργασίες</a>
            <a href="#s10" class="list-group-item list-group-item-action">10. Roadmap</a>
        </div>
    </div>
</div>

<!-- Content -->
<div class="col-lg-10">

<!-- ─── SECTION 1: ΑΝΑΛΥΣΗ ΑΓΟΡΑΣ ─────────────────────────────────────────── -->
<div id="s1" class="plan-section section-anchor">
    <h2><i class="bi bi-map me-2"></i>1. Ανάλυση Αγοράς — Αιτωλοακαρνανία &amp; Ελλάδα</h2>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="kpi-box"><div class="val">~280.000</div><div class="lbl">Κάτοικοι Π.Ε. Αιτωλοακαρνανίας</div></div></div>
        <div class="col-md-3"><div class="kpi-box"><div class="val">&gt;12.000</div><div class="lbl">Εγγεγραμμένες επιχειρήσεις στην περιοχή</div></div></div>
        <div class="col-md-3"><div class="kpi-box"><div class="val">&lt;15%</div><div class="lbl">Έχουν eshop ή σύγχρονη ψηφιακή παρουσία</div></div></div>
        <div class="col-md-3"><div class="kpi-box"><div class="val">~800.000</div><div class="lbl">ΜΜΕ σε όλη την Ελλάδα χωρίς eshop</div></div></div>
    </div>

    <h3>Κύριες Πόλεις — Στόχοι Phase 1</h3>
    <div class="row g-2 mb-3">
        <?php
        $cities = [
            ['Αγρίνιο','~50.000 κάτ. — Μεγαλύτερο αστικό κέντρο, έντονη εμπορική δραστηριότητα, λαϊκές αγορές, franchise, τοπικά brand','phase-1'],
            ['Μεσολόγγι','~10.000 κάτ. — Διοικητική πρωτεύουσα, τουρισμός, αλιεία, αγρι/τουρισμός, ξενοδοχεία','phase-1'],
            ['Ναύπακτος','~15.000 κάτ. — Τουρισμός, εστίαση, ξενοδοχεία, τοπικά καταστήματα','phase-1'],
            ['Αμφιλοχία','~5.000 κάτ. — Τουρισμός λίμνης, αγροτικές επιχειρήσεις','phase-1'],
            ['Θέρμο / Αστακός','Μικρότερα κέντρα, αγρι/τουρισμός, ψαράδικα, τοπικά καφέ','phase-1'],
        ];
        foreach ($cities as [$name,$desc,$ph]): ?>
        <div class="col-md-6">
            <div class="channel-card">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong><?= $name ?></strong>
                    <span class="phase-badge <?= $ph ?>">Phase 1</span>
                </div>
                <div class="small text-muted"><?= $desc ?></div>
            </div>
        </div>
        <?php endforeach ?>
    </div>

    <h3>Κλάδοι Στόχοι — Ποιες Επιχειρήσεις Χρειάζονται Eshop / Website</h3>
    <table class="plan-table">
        <thead><tr><th>Κλάδος</th><th>Τι Χρειάζεται</th><th>Μέσο Deal</th><th>Προτεραιότητα</th></tr></thead>
        <tbody>
        <tr><td><i class="bi bi-shop text-primary me-1"></i> Καταστήματα Λιανικής (ρούχα, παπούτσια, gadgets)</td><td>Eshop, Google Ads, SEO</td><td>€1.400</td><td><span class="badge bg-danger">Πολύ Υψηλή</span></td></tr>
        <tr><td><i class="bi bi-cup-hot text-warning me-1"></i> Εστιατόρια, Ταβέρνες, Καφέ</td><td>Website + online μενού + κρατήσεις</td><td>€700</td><td><span class="badge bg-danger">Πολύ Υψηλή</span></td></tr>
        <tr><td><i class="bi bi-building text-success me-1"></i> Ξενοδοχεία, Ενοικιαζόμενα, Agrotourism</td><td>Website + σύστημα κρατήσεων</td><td>€2.000</td><td><span class="badge bg-danger">Πολύ Υψηλή</span></td></tr>
        <tr><td><i class="bi bi-tree text-success me-1"></i> Αγροτικές / Τοπικά Προϊόντα (ελαιόλαδο, κρασί, τυρί)</td><td>Eshop για online πωλήσεις, B2B portal</td><td>€1.600</td><td><span class="badge bg-warning text-dark">Υψηλή</span></td></tr>
        <tr><td><i class="bi bi-heart-pulse text-danger me-1"></i> Ιατρεία, Οδοντίατροι, Κλινικές</td><td>Website + σύστημα ραντεβού</td><td>€1.100</td><td><span class="badge bg-warning text-dark">Υψηλή</span></td></tr>
        <tr><td><i class="bi bi-briefcase text-info me-1"></i> Δικηγόροι, Λογιστές, Μηχανικοί</td><td>Professional website, blog, SEO</td><td>€800</td><td><span class="badge bg-warning text-dark">Υψηλή</span></td></tr>
        <tr><td><i class="bi bi-scissors text-purple me-1"></i> Beauty, Κομμωτήρια, Γυμναστήρια</td><td>Website + online κρατήσεις</td><td>€750</td><td><span class="badge bg-info text-dark">Μεσαία</span></td></tr>
        <tr><td><i class="bi bi-car-front text-secondary me-1"></i> Συνεργεία, Αντιπροσωπείες, Real Estate</td><td>Catalogue website, CRM integration</td><td>€1.200</td><td><span class="badge bg-info text-dark">Μεσαία</span></td></tr>
        <tr><td><i class="bi bi-tools text-secondary me-1"></i> Κατασκευαστικές, Υδραυλικοί, Ηλεκτρολόγοι</td><td>Portfolio website, SEO τοπικό</td><td>€600</td><td><span class="badge bg-secondary">Κανονική</span></td></tr>
        </tbody>
    </table>

    <div class="alert alert-info mt-3 mb-0 small">
        <i class="bi bi-lightbulb-fill me-1"></i>
        <strong>Βασικό Insight:</strong> Πάνω από το 70% των τοπικών επιχειρήσεων στην επαρχία είτε δεν έχουν website, είτε έχουν απαρχαιωμένο site που δεν εμφανίζεται στο Google. Αυτό είναι το κενό που γεμίζουμε.
    </div>
</div>

<!-- ─── SECTION 2: ΥΠΗΡΕΣΙΕΣ & ΤΙΜΟΛΟΓΗΣΗ ─────────────────────────────────── -->
<div id="s2" class="plan-section section-anchor">
    <h2><i class="bi bi-tags me-2"></i>2. Υπηρεσίες &amp; Τιμολόγηση</h2>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <h3>Πακέτα Υπηρεσιών</h3>
            <table class="plan-table">
                <thead><tr><th>Πακέτο</th><th>Τιμή</th><th>Κατάλληλο Για</th></tr></thead>
                <tbody>
                <tr><td><span class="badge bg-secondary me-1">Starter</span> Landing Page</td><td class="fw-bold">€350 – €550</td><td>Επαγγελματίες, πολύ μικρές επιχ.</td></tr>
                <tr><td><span class="badge bg-primary me-1">Business</span> Website 5–8 σελίδων</td><td class="fw-bold">€600 – €1.100</td><td>Επιχειρήσεις παροχής υπηρεσιών</td></tr>
                <tr><td><span class="badge bg-success me-1">E-Shop</span> Basic Eshop</td><td class="fw-bold">€900 – €1.800</td><td>Λιανική, τοπικά προϊόντα</td></tr>
                <tr><td><span class="badge bg-warning text-dark me-1">E-Shop Pro</span> Πλήρες Eshop</td><td class="fw-bold">€1.800 – €3.500</td><td>Εμπόριο, B2C πώληση προϊόντων</td></tr>
                <tr><td><span class="badge bg-info text-dark me-1">Booking</span> Σύστημα Κρατήσεων</td><td class="fw-bold">€1.200 – €2.500</td><td>Ξενοδοχεία, ιατρεία, εστίαση</td></tr>
                <tr><td><span class="badge bg-danger me-1">Custom</span> Custom Web App</td><td class="fw-bold">€2.500 – €12.000+</td><td>Επιχειρήσεις με ειδικές ανάγκες</td></tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h3>Μηνιαίες Υπηρεσίες (Recurring Revenue)</h3>
            <table class="plan-table">
                <thead><tr><th>Υπηρεσία</th><th>Τιμή/μήνα</th><th>Σημείωση</th></tr></thead>
                <tbody>
                <tr><td>Hosting + Συντήρηση</td><td>€30 – €60</td><td>Κρίσιμο για recurring revenue</td></tr>
                <tr><td>SEO (τοπικό)</td><td>€150 – €350</td><td>Εξαιρετικό upsell για eshops</td></tr>
                <tr><td>Google Ads Management</td><td>€150 – €400 + budget</td><td>Για eshops που θέλουν πωλήσεις</td></tr>
                <tr><td>Social Media Management</td><td>€200 – €500</td><td>Πολύ ζητούμενο από εστίαση</td></tr>
                <tr><td>Email Marketing</td><td>€80 – €200</td><td>Για eshops, ξενοδοχεία</td></tr>
                </tbody>
            </table>
            <div class="alert alert-success mt-2 small mb-0">
                <i class="bi bi-graph-up-arrow me-1"></i>
                <strong>Στόχος:</strong> Κάθε νέος πελάτης = τουλάχιστον €40/μήνα recurring. Με 50 πελάτες → €2.000+/μήνα παθητικό εισόδημα.
            </div>
        </div>
    </div>

    <h3>Τακτική Τιμολόγησης για Επαρχία</h3>
    <div class="row g-2">
        <div class="col-md-4"><div class="channel-card"><strong>Αποφύγετε τις πολύ χαμηλές τιμές</strong><div class="small text-muted mt-1">Δημιουργούν εντύπωση κακής ποιότητας. Η αξία πρέπει να δικαιολογείται με case studies και αποτελέσματα.</div></div></div>
        <div class="col-md-4"><div class="channel-card green"><strong>Προσφορά "Πρώτος στην Περιοχή"</strong><div class="small text-muted mt-1">-20% για τους πρώτους 5 πελάτες κάθε κλάδου σε κάθε πόλη. Δημιουργεί urgency και τοπικό αποκλεισμό.</div></div></div>
        <div class="col-md-4"><div class="channel-card orange"><strong>Δόσεις &amp; Ευέλικτη Πληρωμή</strong><div class="small text-muted mt-1">50% κατά την παραγγελία, 50% κατά την παράδοση. Ή 3 δόσεις για deals άνω των €1.500.</div></div></div>
    </div>
</div>

<!-- ─── SECTION 3: ΕΥΡΕΣΗ LEADS ────────────────────────────────────────────── -->
<div id="s3" class="plan-section section-anchor">
    <h2><i class="bi bi-search me-2"></i>3. Εύρεση &amp; Συλλογή Leads</h2>

    <h3><span class="phase-badge phase-1 me-2">Βήμα 1</span> Πηγές για Αιτωλοακαρνανία</h3>
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <div class="channel-card"><strong><i class="bi bi-google me-1"></i> Google Maps (Πρωτεύουσα Πηγή)</strong>
                <div class="small text-muted mt-1">Αναζήτηση π.χ. <em>"εστιατόρια Αγρίνιο"</em>, <em>"κομμωτήριο Μεσολόγγι"</em>, <em>"ξενοδοχείο Ναύπακτος"</em>. Κάθε αποτέλεσμα που <strong>δεν έχει website</strong> στο Google My Business είναι hot lead. Εξαγωγή τηλεφώνων και import στο CRM.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card green"><strong><i class="bi bi-journal-text me-1"></i> Επιμελητήριο Αιτωλοακαρνανίας</strong>
                <div class="small text-muted mt-1">Δημόσια λίστα εγγεγραμμένων επιχειρήσεων. Επίσης τα Επιμελητήρια ανακοινώνουν εκδηλώσεις — εκεί συναντάτε τους επιχειρηματίες face-to-face. Επικοινωνήστε για συνεργασία ή χορηγία εκδήλωσης.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card orange"><strong><i class="bi bi-phone me-1"></i> XO.gr / Vrisko.gr / BusinessDB</strong>
                <div class="small text-muted mt-1">Ελληνικοί κατάλογοι επιχειρήσεων. Αναζήτηση ανά ΤΚ ή πόλη + κλάδο. Εξαγωγή ονόματος, τηλεφώνου, διεύθυνσης. Import bulk στο CRM μέσω Excel (υπάρχει η λειτουργία).</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card purple"><strong><i class="bi bi-facebook me-1"></i> Facebook Groups &amp; Pages</strong>
                <div class="small text-muted mt-1">Ομάδες: <em>"Επιχειρήσεις Αγρινίου"</em>, <em>"Αγρίνιο Today"</em>, τοπικά marketplace groups. Επιχειρήσεις που ανακοινώνουν χωρίς website είναι prime targets. DM ή τηλεφωνική επικοινωνία.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card teal"><strong><i class="bi bi-instagram me-1"></i> Instagram Τοπικά Hashtags</strong>
                <div class="small text-muted mt-1">#αγρινιο #μεσολογγι #ναυπακτος + κλάδος. Επιχειρήσεις με ενεργό Instagram αλλά χωρίς website — χρειάζονται landing page τουλάχιστον. Πολύ εύκολο pitch.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card"><strong><i class="bi bi-newspaper me-1"></i> Τοπικές Εφημερίδες &amp; Ραδιόφωνο</strong>
                <div class="small text-muted mt-1">Εφημερίδα "Πελοπόννησος", τοπικά sites (aitoloakarnania.gr κ.α.). Επιχειρήσεις που διαφημίζονται εκεί δείχνουν ότι επενδύουν σε marketing — ιδανικοί υποψήφιοι για ψηφιακή αναβάθμιση.</div>
            </div>
        </div>
    </div>

    <h3><span class="phase-badge phase-2 me-2">Βήμα 2</span> Qualifίκαρισμα Lead (Τι να Ελέγχετε Πριν την Κλήση)</h3>
    <div class="row g-2 mb-3">
        <div class="col-sm-6 col-lg-3"><div class="kpi-box"><i class="bi bi-x-circle-fill text-danger fs-4"></i><div class="lbl mt-1">Δεν έχει website ή έχει <strong>πριν το 2018</strong></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="kpi-box"><i class="bi bi-check-circle-fill text-success fs-4"></i><div class="lbl mt-1">Ενεργό Facebook / Instagram (σημαίνει ότι επενδύει)</div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="kpi-box"><i class="bi bi-shop text-primary fs-4"></i><div class="lbl mt-1">Κλάδος υψηλής προτεραιότητας (βλ. Πίνακα §1)</div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="kpi-box"><i class="bi bi-geo-alt text-warning fs-4"></i><div class="lbl mt-1">Βρίσκεται σε κεντρικό σημείο / έχει φυσική βιτρίνα</div></div></div>
    </div>

    <h3><span class="phase-badge phase-3 me-2">Βήμα 3</span> Ροή Εισαγωγής στο CRM</h3>
    <div class="script-box">
        <div class="timeline-row"><div class="timeline-dot"></div><div><strong>Συλλογή τηλεφώνων &amp; ονομάτων</strong> από Google Maps / xo.gr / Facebook</div></div>
        <div class="timeline-row"><div class="timeline-dot green"></div><div><strong>Import μέσω Excel</strong> — Admin → Εισαγωγή Excel (υπάρχει η λειτουργία στο σύστημα)</div></div>
        <div class="timeline-row"><div class="timeline-dot orange"></div><div><strong>Μαζική Ανάθεση</strong> — Admin → Επιχειρήσεις → Μαζική Ανάθεση → επιλογή τηλεφωνητή + τυχαία ανάθεση N επιχειρήσεων</div></div>
        <div class="timeline-row"><div class="timeline-dot purple"></div><div><strong>Τηλεφωνητές καλούν</strong>, καταγράφουν αποτέλεσμα (interested / follow_up / not_interested) στο σύστημα</div></div>
        <div class="timeline-row"><div class="timeline-dot green"></div><div><strong>Ενδιαφερόμενος</strong> → Νέα Συμφωνία → Ανάθεση σε Developer → Έργο</div></div>
    </div>
</div>

<!-- ─── SECTION 4: ΔΙΑΔΙΚΑΣΙΑ ΠΩΛΗΣΕΩΝ ────────────────────────────────────── -->
<div id="s4" class="plan-section section-anchor">
    <h2><i class="bi bi-funnel me-2"></i>4. Διαδικασία Πωλήσεων — Από Lead σε Πελάτη</h2>

    <div class="row g-3">
        <div class="col-md-12">
            <table class="plan-table">
                <thead><tr><th>Στάδιο</th><th>Ενέργεια</th><th>Εργαλείο στο CRM</th><th>Στόχος Χρόνου</th></tr></thead>
                <tbody>
                <tr><td><span class="badge phase-1 phase-badge">1</span> Νέο Lead</td><td>Εισαγωγή στο σύστημα — company_name, τηλέφωνο, κλάδος, πόλη</td><td>Import Excel ή Χειροκίνητη Εισαγωγή</td><td>Άμεσα</td></tr>
                <tr><td><span class="badge phase-1 phase-badge">2</span> Πρώτη Επαφή</td><td>Τηλεφωνική κλήση — εισαγωγή, εντοπισμός ανάγκης, ποιότητα lead</td><td>Καταγραφή Interaction (status → "contacted")</td><td>Εντός 24ω από την εισαγωγή</td></tr>
                <tr><td><span class="badge phase-2 phase-badge">3</span> Ενδιαφέρον</td><td>Αποστολή email με portfolio + τιμές. Ορισμός follow-up κλήσης</td><td>Status → "interested", 2η Interaction</td><td>Εντός 48ω</td></tr>
                <tr><td><span class="badge phase-2 phase-badge">4</span> Follow-Up</td><td>2η κλήση — απαντήσεις σε ερωτήσεις, προσφορά, κλείσιμο ραντεβού ή deal</td><td>3η Interaction, Status → "follow_up"</td><td>3–5 μέρες αργότερα</td></tr>
                <tr><td><span class="badge phase-3 phase-badge">5</span> Προσφορά</td><td>Αποστολή επίσημης προσφοράς PDF. Ξεκάθαρο τι περιλαμβάνεται, timeline, τιμή</td><td>Καταγραφή στο notes της επιχείρησης</td><td>Εντός 24ω από το follow-up</td></tr>
                <tr><td><span class="badge phase-3 phase-badge">6</span> Κλείσιμο</td><td>Υπογραφή σύμβασης, 50% προκαταβολή, εισαγωγή Deal στο σύστημα</td><td>Νέα Συμφωνία (status: approved), Upload contract</td><td>Ιδανικά εντός 7 ημερών από την προσφορά</td></tr>
                <tr><td><span class="badge phase-4 phase-badge">7</span> Ανάπτυξη</td><td>Ανάθεση σε developer, δημιουργία Project, φάσεις, deadlines</td><td>Admin → Έργα → Νέο Έργο</td><td>Εκκίνηση εντός 72ω από την πληρωμή</td></tr>
                <tr><td><span class="badge phase-4 phase-badge">8</span> Παράδοση &amp; Upsell</td><td>Παράδοση, training, υπογραφή για hosting+SEO maintenance</td><td>Deal status → "completed", νέες Commissions</td><td>Σύμφωνα με project timeline</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-warning mt-3 mb-0 small">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <strong>Κρίσιμο:</strong> Κάθε "όχι" είναι πληροφορία. Καταγράφετε <em>πάντα</em> τον λόγο απόρριψης στο σύστημα (notes). Πολλά "όχι λόγω τιμής" = αναθεώρηση pricing. Πολλά "δεν το χρειάζομαι" = αλλαγή pitch.
    </div>
</div>

<!-- ─── SECTION 5: SCRIPT ΚΛΗΣΕΩΝ ─────────────────────────────────────────── -->
<div id="s5" class="plan-section section-anchor">
    <h2><i class="bi bi-telephone me-2"></i>5. Script Τηλεφωνικής Κλήσης</h2>

    <h3>Πρώτη Κλήση — Εστιατόριο / Κατάστημα Λιανικής</h3>
    <div class="script-box mb-3">
        <div class="mb-2"><span class="caller">[Τηλεφωνητής]:</span> <span class="prospect">«Καλημέρα σας, μιλώ με τον/την κύριο/κυρία [Επώνυμο]; Είμαι ο/η [Όνομα] από την SoftSystems, μια εταιρία ψηφιακών λύσεων. Σας παίρνω γιατί είδαμε ότι [επιχείρησή σας] έχει υπέροχη παρουσία στο [Facebook/Instagram] και θέλαμε να μιλήσουμε μαζί σας για μια ευκαιρία. Έχετε 2 λεπτά;»</span></div>
        <div class="mb-2"><span class="caller">[Αν ναι]:</span> <span class="prospect">«Τελευταία έχουμε δει πολλές επιχειρήσεις στο [Αγρίνιο/Μεσολόγγι] να χάνουν πελάτες γιατί οι ανταγωνιστές τους εμφανίζονται πρώτοι στη Google. Εσείς έχετε website ή eshop αυτή τη στιγμή;»</span></div>
        <div class="mb-2"><span class="caller">[Αν όχι/παλιό site]:</span> <span class="prospect">«Ακριβώς αυτό θέλαμε να σας μιλήσουμε. Κάνουμε επαγγελματικές ιστοσελίδες και eshops ειδικά για επιχειρήσεις σαν τη δική σας — με τιμές από €500, έτοιμο σε 3 εβδομάδες, και εγγύηση αποτελεσμάτων στη Google. Θα σας ενδιέφερε να σας στείλω μια σύντομη παρουσίαση με παραδείγματα από [κλάδος σας];»</span></div>
        <div class="mb-2"><span class="caller">[Αν ναι]:</span> <span class="prospect">«Τέλεια. Σε ποιο email να σας στείλω; Και πότε σας βολεύει να μιλήσουμε ξανά αυτή την εβδομάδα;»</span></div>
        <div><span class="caller">[Καταγραφή]:</span> Email, ημέρα/ώρα follow-up, κατάσταση → "interested" ή "follow_up" στο CRM.</div>
    </div>

    <h3>Προσέγγιση για Ξενοδοχεία / Agrotourism</h3>
    <div class="script-box mb-3">
        <div class="mb-2"><span class="caller">[Τηλεφωνητής]:</span> <span class="prospect">«Καλησπέρα, μιλάω για το [Κατάλυμα/Ξενοδοχείο]. Είδα ότι είστε στο Booking.com αλλά δεν έχετε δική σας ιστοσελίδα — σωστά;»</span></div>
        <div class="mb-2"><span class="prospect">«Με τη δική σας ιστοσελίδα, οι πελάτες κλείνουν <strong>απευθείας</strong> χωρίς να πληρώνετε προμήθεια στο Booking (15–20% ανά κράτηση). Για ένα κατάλυμα με 20 κρατήσεις τον μήνα × €80 μέση τιμή, μιλάμε για €240–320/μήνα εξοικονόμηση. Η ιστοσελίδα αποσβένεται σε λιγότερο από 4 μήνες.»</span></div>
    </div>

    <h3>Προσέγγιση για Επαγγελματίες (Ιατροί / Δικηγόροι)</h3>
    <div class="script-box">
        <div class="prospect">«Σήμερα 8 στους 10 ασθενείς/πελάτες ψάχνουν Google πριν επιλέξουν γιατρό/δικηγόρο. Χωρίς site, χάνετε αυτούς που ψάχνουν "[ειδικότητα] [πόλη]" — και αυτοί πάνε στον ανταγωνιστή σας που εμφανίζεται πρώτος. Εμείς φτιάχνουμε ακριβώς αυτό: απλό, επαγγελματικό site με ραντεβού online.»</div>
    </div>
</div>

<!-- ─── SECTION 6: ΑΝΤΙΡΡΗΣΕΙΣ ────────────────────────────────────────────── -->
<div id="s6" class="plan-section section-anchor">
    <h2><i class="bi bi-shield-check me-2"></i>6. Αντιμετώπιση Αντιρρήσεων</h2>
    <div class="row g-2">
        <div class="col-md-6">
            <div class="objection">
                <strong>«Δεν με ενδιαφέρει / δεν το χρειάζομαι»</strong>
                <div class="answer">«Το καταλαβαίνω. Πολλοί πελάτες μας είπαν το ίδιο πριν δουν ότι ο ανταγωνιστής τους ανέβαζε πελατεία από τη Google ενώ εκείνοι έχαναν. Αν έχετε 2 λεπτά, σας δείχνω τι τράβαγε traffic τον κλάδο σας τον τελευταίο μήνα — χωρίς δέσμευση.»</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="objection">
                <strong>«Είναι ακριβό»</strong>
                <div class="answer">«Κατανοητό. Εμείς όμως δεν είμαστε κόστος — είμαστε επένδυση. Αν το site φέρει έστω 2 νέους πελάτες τον μήνα (πολύ συντηρητικό), έχει αποσβεστεί σε 3–4 μήνες. Και υπάρχει η επιλογή πληρωμής σε δόσεις.»</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="objection">
                <strong>«Έχω ήδη κάποιον να μου το κάνει»</strong>
                <div class="answer">«Χαίρομαι που ακούω ότι επενδύετε στο ψηφιακό. Μπορώ να ρωτήσω πότε αναμένεται έτοιμο; Αν για οποιονδήποτε λόγο δεν προχωρήσει, ή θέλετε δεύτερη γνώμη, είμαστε εδώ.»</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="objection">
                <strong>«Δεν έχω χρόνο τώρα»</strong>
                <div class="answer">«Φυσικά, σέβομαι τον χρόνο σας. Πότε σας βολεύει να μιλήσουμε; Η κλήση δεν θα ξεπεράσει 5 λεπτά — απλά θέλω να σας δείξω ένα παράδειγμα από έναν ομοειδή στην περιοχή σας.»</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="objection">
                <strong>«Έχω Facebook, δεν χρειάζομαι site»</strong>
                <div class="answer">«Το Facebook είναι εξαιρετικό για engagement, αλλά το Google αναζητεί websites — όχι Facebook pages. Όταν κάποιος ψάχνει "[υπηρεσία σας] [πόλη]", το Facebook δεν εμφανίζεται. Χάνετε τους "ψάχτες" που είναι έτοιμοι να αγοράσουν τώρα.»</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="objection">
                <strong>«Δεν ξέρω τεχνολογία — δεν θα μπορώ να το χειριστώ»</strong>
                <div class="answer">«Ακριβώς γι' αυτό εμείς αναλαμβάνουμε τα πάντα — hosting, ανανεώσεις, backup. Εσείς δεν χρειάζεται να κάνετε τίποτα. Αν θέλετε να αλλάξετε μια φωτογραφία, μας στέλνετε WhatsApp και γίνεται αυθημερόν.»</div>
            </div>
        </div>
    </div>
</div>

<!-- ─── SECTION 7: KPIs ─────────────────────────────────────────────────────── -->
<div id="s7" class="plan-section section-anchor">
    <h2><i class="bi bi-bar-chart-line me-2"></i>7. KPIs, Στόχοι &amp; Αξιολόγηση</h2>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="kpi-box"><div class="val">80–120</div><div class="lbl">Κλήσεις/ημέρα ανά τηλεφωνητή</div></div></div>
        <div class="col-md-3"><div class="kpi-box"><div class="val">6–10%</div><div class="lbl">Ποσοστό μετατροπής σε "interested"</div></div></div>
        <div class="col-md-3"><div class="kpi-box"><div class="val">25–35%</div><div class="lbl">Ποσοστό κλεισίματος από "interested"</div></div></div>
        <div class="col-md-3"><div class="kpi-box"><div class="val">€1.100</div><div class="lbl">Μέσο μέγεθος deal (στόχος)</div></div></div>
    </div>

    <h3>Μηνιαίοι Στόχοι ανά Επίπεδο Ανάπτυξης</h3>
    <table class="plan-table">
        <thead><tr><th>Φάση</th><th>Τηλεφωνητές</th><th>Κλήσεις/μήνα</th><th>Deals/μήνα</th><th>Στόχος Εσόδων</th></tr></thead>
        <tbody>
        <tr><td><span class="phase-badge phase-1">Εκκίνηση (μήνας 1–2)</span></td><td>1–2</td><td>~2.000</td><td>3–5</td><td>€3.000 – €5.500</td></tr>
        <tr><td><span class="phase-badge phase-2">Ανάπτυξη (μήνας 3–6)</span></td><td>3–4</td><td>~6.000</td><td>10–15</td><td>€11.000 – €16.500</td></tr>
        <tr><td><span class="phase-badge phase-3">Κλιμάκωση (μήνας 7–12)</span></td><td>6–8</td><td>~14.000</td><td>25–35</td><td>€27.500 – €38.500</td></tr>
        <tr><td><span class="phase-badge phase-4">Εθνικό (έτος 2+)</span></td><td>10+</td><td>~25.000+</td><td>50+</td><td>€55.000+/μήνα</td></tr>
        </tbody>
    </table>

    <h3>Αξιολόγηση Τηλεφωνητών (Εβδομαδιαία)</h3>
    <table class="plan-table">
        <thead><tr><th>Δείκτης</th><th>Πολύ Καλό</th><th>Αποδεκτό</th><th>Χρειάζεται Βελτίωση</th></tr></thead>
        <tbody>
        <tr><td>Κλήσεις / εβδομάδα</td><td>&gt; 450</td><td>300–450</td><td>&lt; 300</td></tr>
        <tr><td>% "interested" leads</td><td>&gt; 8%</td><td>5–8%</td><td>&lt; 5%</td></tr>
        <tr><td>% deals κλεισίματος</td><td>&gt; 30%</td><td>20–30%</td><td>&lt; 20%</td></tr>
        <tr><td>Μέσος χρόνος κλήσης</td><td>2–4 λεπτά</td><td>1.5–5 λεπτά</td><td>&lt; 1 λεπτό ή &gt; 7 λεπτά</td></tr>
        </tbody>
    </table>
</div>

<!-- ─── SECTION 8: DIGITAL MARKETING ─────────────────────────────────────────── -->
<div id="s8" class="plan-section section-anchor">
    <h2><i class="bi bi-megaphone me-2"></i>8. Digital Marketing — Inbound Leads</h2>
    <p class="text-muted small mb-3">Παράλληλα με το cold calling, τα παρακάτω κανάλια φέρνουν leads που <strong>ψάχνουν ήδη</strong> την υπηρεσία μας — πολύ πιο εύκολα να κλείσουν.</p>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="channel-card">
                <strong><i class="bi bi-google me-1"></i> Google My Business (ΔΩΡΕΑΝ — Άμεσο Priority)</strong>
                <div class="small text-muted mt-1">
                    Δημιουργήστε / βελτιστοποιήστε το GMB profile της εταιρίας. Φωτογραφίες πριν/μετά, reviews, κατηγορία "Web Design". Οποιοσδήποτε ψάχνει "κατασκευή ιστοσελίδας [πόλη]" βλέπει εσάς πρώτοι.
                    <div class="mt-1"><span class="badge bg-success">Κόστος: €0</span> <span class="badge bg-primary ms-1">Αποτέλεσμα σε: 2–4 εβδομάδες</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card green">
                <strong><i class="bi bi-laptop me-1"></i> SEO Website Εταιρίας</strong>
                <div class="small text-muted mt-1">
                    Στοχεύστε keywords: <em>"κατασκευή eshop Αγρίνιο"</em>, <em>"ιστοσελίδα Αιτωλοακαρνανία"</em>, <em>"web developer Ελλάδα"</em>. Blog με case studies ("Πώς αυξήσαμε τις πωλήσεις ενός εστιατορίου στο Αγρίνιο 40%"). Low competition = γρήγορα αποτελέσματα.
                    <div class="mt-1"><span class="badge bg-warning text-dark">Κόστος: χρόνος</span> <span class="badge bg-primary ms-1">Αποτέλεσμα σε: 2–4 μήνες</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card orange">
                <strong><i class="bi bi-meta me-1"></i> Facebook / Instagram Ads (Τοπικές Καμπάνιες)</strong>
                <div class="small text-muted mt-1">
                    Targeting: επιχειρηματίες &gt;28 ετών, Αιτωλοακαρνανία, interests: "small business", "e-commerce". Lead generation ads: "Θέλετε eshop; Δωρεάν εκτίμηση τιμής →". Budget €150–300/μήνα για αρχή.
                    <div class="mt-1"><span class="badge bg-danger">Κόστος: €150–300/μήνα</span> <span class="badge bg-primary ms-1">Αποτέλεσμα σε: 1–2 εβδομάδες</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card purple">
                <strong><i class="bi bi-youtube me-1"></i> YouTube / TikTok — Εκπαιδευτικό Content</strong>
                <div class="small text-muted mt-1">
                    Videos: <em>"Γιατί το eshop σας πρέπει να εμφανίζεται στη Google"</em>, <em>"5 λάθη που κοστίζουν πελάτες στο εστιατόριό σου"</em>. Builds trust, αυξάνει inbound. Μεσοπρόθεσμη στρατηγική αλλά εξαιρετικά cost-effective.
                    <div class="mt-1"><span class="badge bg-success">Κόστος: χρόνος</span> <span class="badge bg-warning text-dark ms-1">Αποτέλεσμα σε: 3–6 μήνες</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card teal">
                <strong><i class="bi bi-linkedin me-1"></i> LinkedIn — B2B &amp; Εθνικό Κοινό</strong>
                <div class="small text-muted mt-1">
                    Για μεγαλύτερες εταιρίες, franchise, chains. Posts με case studies, αποτελέσματα, πριν/μετά. Connect με CEOs, managers, ιδιοκτήτες επιχειρήσεων. Outreach message script παρόμοιο με το τηλεφωνικό.
                    <div class="mt-1"><span class="badge bg-success">Κόστος: €0 (organic)</span> <span class="badge bg-primary ms-1">Κοινό: B2B Ελλάδα</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="channel-card">
                <strong><i class="bi bi-envelope-at me-1"></i> Email Marketing — Καμπάνιες σε Leads</strong>
                <div class="small text-muted mt-1">
                    Collect emails από leads που δεν απάντησαν στο τηλέφωνο. Sequence: Email 1 (αξία), Email 2 (case study), Email 3 (προσφορά περιορισμένου χρόνου). Εργαλεία: Brevo (Sendinblue) δωρεάν έως 300/μέρα.
                    <div class="mt-1"><span class="badge bg-success">Κόστος: €0–€25/μήνα</span> <span class="badge bg-info text-dark ms-1">Συμπληρωματικό κανάλι</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ─── SECTION 9: ΣΥΝΕΡΓΑΣΙΕΣ ────────────────────────────────────────────── -->
<div id="s9" class="plan-section section-anchor">
    <h2><i class="bi bi-people-fill me-2"></i>9. Στρατηγικές Συνεργασίες &amp; Referral Network</h2>

    <p class="text-muted small mb-3">Οι referrals από εμπιστευόμενες πηγές κλείνουν <strong>5x πιο εύκολα</strong> από cold leads και έχουν <strong>μηδενικό κόστος απόκτησης</strong>.</p>

    <table class="plan-table">
        <thead><tr><th>Τύπος Συνεργάτη</th><th>Γιατί Λειτουργεί</th><th>Πρόταση Συνεργασίας</th><th>Προμήθεια</th></tr></thead>
        <tbody>
        <tr>
            <td><strong><i class="bi bi-calculator me-1"></i> Λογιστές</strong></td>
            <td>Κάθε νέα επιχείρηση που ανοίγει ΑΦΜ περνά από λογιστή. Χρειάζεται site/eshop.</td>
            <td>«Αν παραπέμψεις πελάτη σου, λαμβάνεις 10% του deal σαν συνεργάτης.» Δώστε τους κάρτες και brochures.</td>
            <td>10% deal value</td>
        </tr>
        <tr>
            <td><strong><i class="bi bi-briefcase me-1"></i> Δικηγόροι Εμπορικού</strong></td>
            <td>Χειρίζονται ιδρύσεις εταιριών, M&A, νέες επιχειρήσεις που χρειάζονται ψηφιακή παρουσία.</td>
            <td>Επαγγελματική συμφωνία referral. Μπορείτε να τους φτιάξετε δωρεάν site ως αντάλλαγμα για αποκλειστική παραπομπή.</td>
            <td>10–15%</td>
        </tr>
        <tr>
            <td><strong><i class="bi bi-building-gear me-1"></i> Επιμελητήριο Αιτωλ/νίας</strong></td>
            <td>Εκπροσωπεί ΟΛΕΣ τις επιχειρήσεις της περιοχής. Workshops, εκδηλώσεις, ανακοινώσεις.</td>
            <td>Προσφορά ειδικής τιμής σε μέλη (15% έκπτωση) + χορηγία εκδήλωσης = άμεση έκθεση σε δεκάδες επιχειρηματίες.</td>
            <td>—</td>
        </tr>
        <tr>
            <td><strong><i class="bi bi-phone me-1"></i> Κατασκευαστές Mobile Apps / Graphic Designers</strong></td>
            <td>Δεν κάνουν web development — παραπέμπουν πελάτες που το ζητούν.</td>
            <td>Αμοιβαίο referral agreement. Εσείς τους στέλνετε clients για design/app, αυτοί σας στέλνουν για websites.</td>
            <td>Αμοιβαίο 10%</td>
        </tr>
        <tr>
            <td><strong><i class="bi bi-printer me-1"></i> Τυπογραφεία &amp; Διαφημιστικά</strong></td>
            <td>Ο πελάτης που φτιάχνει επαγγελματικές κάρτες / φυλλάδια χρειάζεται και ψηφιακή παρουσία.</td>
            <td>Cross-sell partnership: αυτοί βάζουν flyer δικό σας, εσείς τους προτείνετε για print.</td>
            <td>8–10%</td>
        </tr>
        </tbody>
    </table>

    <div class="alert alert-info mt-3 mb-0 small">
        <i class="bi bi-star-fill me-1 text-warning"></i>
        <strong>Σημαντικό:</strong> Οι συνεργάτες καταχωρούνται ως <em>Partners</em> στο σύστημα, παραπέμπουν Referrals που γίνονται Deals, και λαμβάνουν αυτόματα την προμήθειά τους μέσω του Commission module.
    </div>
</div>

<!-- ─── SECTION 10: ROADMAP ────────────────────────────────────────────────── -->
<div id="s10" class="plan-section section-anchor">
    <h2><i class="bi bi-map-fill me-2"></i>10. Roadmap Ανάπτυξης — Από Τοπικό σε Εθνικό</h2>

    <table class="plan-table mb-3">
        <thead><tr><th>Φάση</th><th>Χρονικό Πλαίσιο</th><th>Στόχος</th><th>Ενέργειες</th><th>Εκτιμώμενα Έσοδα/Μήνα</th></tr></thead>
        <tbody>
        <tr>
            <td><span class="phase-badge phase-1">Phase 1</span><br><strong>Αιτωλοακαρνανία</strong></td>
            <td>Μήνες 1–3</td>
            <td>10+ ενεργοί πελάτες, 3+ ongoing projects</td>
            <td>Αγρίνιο + Μεσολόγγι cold calling. Import 500+ επιχειρήσεων. 2 τηλεφωνητές. Εγκατάσταση συνεργατών (2 λογιστές, 1 τυπογραφείο).</td>
            <td>€3.000 – €8.000</td>
        </tr>
        <tr>
            <td><span class="phase-badge phase-2">Phase 2</span><br><strong>Δυτική Ελλάδα</strong></td>
            <td>Μήνες 4–7</td>
            <td>30+ πελάτες, team 4 callers, 2 developers</td>
            <td>Επέκταση σε Πάτρα, Ιωάννινα, Αγρίνιο. Facebook Ads €300/μήνα. SEO site εταιρίας. Portfolio 10+ completed projects.</td>
            <td>€12.000 – €20.000</td>
        </tr>
        <tr>
            <td><span class="phase-badge phase-3">Phase 3</span><br><strong>Κεντρική Ελλάδα + Θεσσαλονίκη</strong></td>
            <td>Μήνες 8–12</td>
            <td>80+ πελάτες, €2.000+/μήνα recurring</td>
            <td>Εθνικές καμπάνιες Google Ads. LinkedIn outreach B2B. Πρόσληψη sales manager. Τυποποιημένα πακέτα ανά κλάδο.</td>
            <td>€25.000 – €45.000</td>
        </tr>
        <tr>
            <td><span class="phase-badge phase-4">Phase 4</span><br><strong>Πανελλαδικό + Online</strong></td>
            <td>Έτος 2+</td>
            <td>200+ πελάτες, €10.000+/μήνα recurring</td>
            <td>White-label partnerships. SaaS component (website builder για SMEs). Franchise model για άλλες περιοχές. Στόχος: €500.000 / χρόνο.</td>
            <td>€50.000+</td>
        </tr>
        </tbody>
    </table>

    <h3>Αμεσότερες Προτεραιότητες (Επόμενες 30 Ημέρες)</h3>
    <div class="row g-2">
        <div class="col-md-4">
            <div class="channel-card green">
                <strong>Εβδομάδα 1–2</strong>
                <ul class="small text-muted mt-1 mb-0 ps-3">
                    <li>Συλλογή 200+ leads Αγρινίου από Google Maps + XO.gr</li>
                    <li>Import στο CRM + ανάθεση σε τηλεφωνητές</li>
                    <li>Δημιουργία/βελτίωση GMB εταιρίας</li>
                    <li>Ετοιμασία portfolio (3–5 παραδείγματα)</li>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="channel-card orange">
                <strong>Εβδομάδα 3–4</strong>
                <ul class="small text-muted mt-1 mb-0 ps-3">
                    <li>Ενεργοποίηση cold calling — 80+ κλήσεις/ημέρα</li>
                    <li>Επικοινωνία Επιμελητηρίου Αιτωλοακαρνανίας</li>
                    <li>Επαφή με 3–5 λογιστές για referral agreement</li>
                    <li>Πρώτες 2–3 κλεισμένες συμφωνίες</li>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="channel-card purple">
                <strong>Μήνας 2</strong>
                <ul class="small text-muted mt-1 mb-0 ps-3">
                    <li>Facebook Ads €150 τοπικά (Αγρίνιο, Μεσολόγγι)</li>
                    <li>Import 300+ leads Μεσολογγίου + Ναυπάκτου</li>
                    <li>Review collection από πρώτους πελάτες (Google + Facebook)</li>
                    <li>Στόχος: 8–10 deals συνολικά στον 2ο μήνα</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="alert alert-success mt-3 mb-0">
        <div class="fw-semibold mb-1"><i class="bi bi-trophy-fill text-warning me-1"></i> Το Ανταγωνιστικό Πλεονέκτημα στην Επαρχία</div>
        <div class="small">Στα μεγάλα αστικά κέντρα ο ανταγωνισμός web agencies είναι σκληρός. Στην Αιτωλοακαρνανία και τη δυτική Ελλάδα υπάρχουν <strong>ελάχιστες τοπικές εταιρίες web development</strong> με οργανωμένη παρουσία. Η τοπική σας γνώση, η άμεση επικοινωνία, και η ικανότητα να επισκέπτεστε φυσικά τον πελάτη αν χρειαστεί είναι ανταγωνιστικά πλεονεκτήματα που καμία Αθηναϊκή agency δεν μπορεί να αντιγράψει εύκολα.
        </div>
    </div>
</div>

</div><!-- /col content -->
</div><!-- /row -->
