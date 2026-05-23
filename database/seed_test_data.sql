-- ══════════════════════════════════════════════════════════════
-- SoftSystems Partnership Portal — Test Data Seed
-- Password for all test accounts: Test1234!
-- ══════════════════════════════════════════════════════════════
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Test Users ──────────────────────────────────────────────
INSERT INTO users (name, email, password, role, is_active, category_id, phone, created_at) VALUES
('Γιώργος Παππάς',       'test.caller@softsystems.gr',    '$2y$10$53ph1S/jH9/61jEQaqJI5uKqQm0D8L8tT2AjbFouWDQQ6LF4/fpje', 'caller',    1, 2, '6971000001', NOW()),
('Νίκος Σταματόπουλος',  'test.developer@softsystems.gr', '$2y$10$53ph1S/jH9/61jEQaqJI5uKqQm0D8L8tT2AjbFouWDQQ6LF4/fpje', 'developer', 1, 1, '6971000002', NOW()),
('Ελένη Μαρκοπούλου',    'test.partner@softsystems.gr',   '$2y$10$53ph1S/jH9/61jEQaqJI5uKqQm0D8L8tT2AjbFouWDQQ6LF4/fpje', 'partner',   1, 2, '6971000003', NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Store IDs in variables
SET @caller_id  = (SELECT id FROM users WHERE email = 'test.caller@softsystems.gr'    LIMIT 1);
SET @dev_id     = (SELECT id FROM users WHERE email = 'test.developer@softsystems.gr' LIMIT 1);
SET @partner_id = (SELECT id FROM users WHERE email = 'test.partner@softsystems.gr'   LIMIT 1);
SET @admin_id   = 1;

-- ── 2. User Roles ───────────────────────────────────────────────
INSERT IGNORE INTO user_roles (user_id, role) VALUES
(@caller_id,  'caller'),
(@dev_id,     'developer'),
(@partner_id, 'partner');

-- ── 3. Caller: Assign Businesses ───────────────────────────────
INSERT IGNORE INTO caller_assignments (business_id, caller_id, assigned_by, assigned_at) VALUES
(1,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(2,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 58 DAY)),
(3,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 55 DAY)),
(4,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(5,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(6,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(7,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 35 DAY)),
(8,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(9,  @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 22 DAY)),
(10, @caller_id, @admin_id, DATE_SUB(NOW(), INTERVAL 15 DAY));

-- ── 4. Interactions ─────────────────────────────────────────────
INSERT INTO interactions (business_id, caller_id, type, result, notes, duration_min, created_at) VALUES
(1, @caller_id, 'call',      'interested',      'Ο πελάτης ενδιαφέρεται για eshop. Θα επικοινωνήσει μετά τις διακοπές.', 12, DATE_SUB(NOW(), INTERVAL 58 DAY)),
(1, @caller_id, 'follow_up', 'callback',        'Ζήτησε προσφορά για eshop + SEO πακέτο.', 8,  DATE_SUB(NOW(), INTERVAL 50 DAY)),
(1, @caller_id, 'email',     'sent',            'Εστάλη προσφορά €4.500 για eshop.', NULL, DATE_SUB(NOW(), INTERVAL 47 DAY)),
(2, @caller_id, 'call',      'not_interested',  'Δεν ενδιαφέρεται αυτή την περίοδο. Επανεπικοινωνία Σεπτέμβριο.', 5,  DATE_SUB(NOW(), INTERVAL 55 DAY)),
(3, @caller_id, 'call',      'interested',      'Ενδιαφέρεται για CRM + eshop combo. Ζήτησε demo.', 18, DATE_SUB(NOW(), INTERVAL 48 DAY)),
(3, @caller_id, 'demo',      'completed',       'Demo ολοκληρώθηκε με επιτυχία. Ο πελάτης θέλει να προχωρήσει.', 45, DATE_SUB(NOW(), INTERVAL 40 DAY)),
(4, @caller_id, 'call',      'no_answer',       NULL, 3, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(4, @caller_id, 'call',      'callback',        'Επανεπικοινωνία Παρασκευή πρωί.', 6, DATE_SUB(NOW(), INTERVAL 43 DAY)),
(5, @caller_id, 'whatsapp',  'interested',      'Στάλθηκε brochure μέσω WhatsApp. Ενδιαφέρεται για website.', NULL, DATE_SUB(NOW(), INTERVAL 35 DAY)),
(6, @caller_id, 'call',      'left_message',    'Αναπάντητο. Αφέθηκε μήνυμα.', 2, DATE_SUB(NOW(), INTERVAL 28 DAY)),
(7, @caller_id, 'call',      'interested',      'Θέλει Mobile App για το κατάστημά του.', 22, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(8, @caller_id, 'email',     'sent',            'Εστάλη email με portfolio.', NULL, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(9, @caller_id, 'call',      'interested',      'Ενδιαφέρεται για ERP σύστημα. Μεγάλη εταιρεία.', 30, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(10, @caller_id,'follow_up', 'callback',        'Συνέχεια από προηγούμενη επαφή. Έτοιμος για υπογραφή.', 15, DATE_SUB(NOW(), INTERVAL 5 DAY));

-- ── 5. Deals ────────────────────────────────────────────────────

-- Deal A: Caller deal — COMPLETED, paid commission
INSERT INTO deals (business_id, caller_id, developer_id, service_id, amount, currency, notes, status, approved_by, approved_at, contract_signed, created_at)
VALUES (1, @caller_id, @dev_id, 2, 4500.00, 'EUR', 'Eshop με WooCommerce. Ο πελάτης επέλεξε το πλήρες πακέτο.', 'completed', @admin_id, DATE_SUB(NOW(), INTERVAL 45 DAY), 1, DATE_SUB(NOW(), INTERVAL 58 DAY));
SET @deal_a = LAST_INSERT_ID();

-- Deal B: Caller deal — IN_PROGRESS
INSERT INTO deals (business_id, caller_id, developer_id, service_id, amount, currency, notes, status, approved_by, approved_at, contract_signed, created_at)
VALUES (3, @caller_id, @dev_id, 8, 7200.00, 'EUR', 'CRM προσαρμοσμένο για εισαγωγή / εξαγωγή.', 'in_progress', @admin_id, DATE_SUB(NOW(), INTERVAL 30 DAY), 1, DATE_SUB(NOW(), INTERVAL 42 DAY));
SET @deal_b = LAST_INSERT_ID();

-- Deal C: Caller deal — APPROVED, unpaid commission pending
INSERT INTO deals (business_id, caller_id, service_id, amount, currency, notes, status, approved_by, approved_at, contract_signed, created_at)
VALUES (9, @caller_id, 7, 12000.00, 'EUR', 'ERP ολοκληρωμένο για εισαγωγή/εξαγωγή εμπορευμάτων.', 'approved', @admin_id, DATE_SUB(NOW(), INTERVAL 8 DAY), 0, DATE_SUB(NOW(), INTERVAL 10 DAY));
SET @deal_c = LAST_INSERT_ID();

-- Deal D: Caller deal — PENDING
INSERT INTO deals (business_id, caller_id, service_id, amount, currency, notes, status, created_at)
VALUES (7, @caller_id, 9, 3800.00, 'EUR', 'Mobile app για αλυσίδα καταστημάτων. Σε αξιολόγηση.', 'pending', DATE_SUB(NOW(), INTERVAL 5 DAY));
SET @deal_d = LAST_INSERT_ID();

-- Deal E: Caller deal — REJECTED
INSERT INTO deals (business_id, caller_id, service_id, amount, currency, notes, status, created_at)
VALUES (2, @caller_id, 3, 1200.00, 'EUR', 'Μικρό marketing πακέτο. Απορρίφθηκε λόγω budget.', 'rejected', DATE_SUB(NOW(), INTERVAL 50 DAY));

-- Deal F: Caller deal — COMPLETED (2nd completed for stats)
INSERT INTO deals (business_id, caller_id, service_id, amount, currency, notes, status, approved_by, approved_at, contract_signed, created_at)
VALUES (5, @caller_id, 1, 2800.00, 'EUR', 'Εταιρικό website με SEO βελτιστοποίηση.', 'completed', @admin_id, DATE_SUB(NOW(), INTERVAL 70 DAY), 1, DATE_SUB(NOW(), INTERVAL 80 DAY));
SET @deal_f = LAST_INSERT_ID();

-- Deal G: Partner referral — COMPLETED, paid partner commission
INSERT INTO deals (business_id, caller_id, partner_id, partner_involvement, developer_id, service_id, amount, currency, notes, status, approved_by, approved_at, contract_signed, created_at)
VALUES (4, @admin_id, @partner_id, 'full_closure', @dev_id, 6, 9500.00, 'EUR', 'Custom software για αποθήκη. Ο συνεργάτης έκλεισε το deal ανεξάρτητα.', 'completed', @admin_id, DATE_SUB(NOW(), INTERVAL 55 DAY), 1, DATE_SUB(NOW(), INTERVAL 65 DAY));
SET @deal_g = LAST_INSERT_ID();

-- Deal H: Partner referral — APPROVED, unpaid
INSERT INTO deals (business_id, caller_id, partner_id, partner_involvement, service_id, amount, currency, notes, status, approved_by, approved_at, contract_signed, created_at)
VALUES (6, @admin_id, @partner_id, 'contact', 2, 6000.00, 'EUR', 'Eshop για εταιρεία τροφίμων. Η συνεργάτης έφερε σε επαφή.', 'approved', @admin_id, DATE_SUB(NOW(), INTERVAL 15 DAY), 0, DATE_SUB(NOW(), INTERVAL 20 DAY));
SET @deal_h = LAST_INSERT_ID();

-- Deal I: Partner referral — PENDING
INSERT INTO deals (business_id, caller_id, partner_id, partner_involvement, service_id, amount, currency, notes, status, created_at)
VALUES (8, @admin_id, @partner_id, 'contact', 5, 2200.00, 'EUR', 'SEO & Social Media πακέτο. Εκκρεμεί αξιολόγηση.', 'pending', DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Deal J: Developer deal — COMPLETED (additional for dev stats)
INSERT INTO deals (business_id, caller_id, developer_id, service_id, amount, currency, notes, status, approved_by, approved_at, contract_signed, created_at)
VALUES (10, @admin_id, @dev_id, 9, 5500.00, 'EUR', 'Mobile app για delivery εταιρεία.', 'completed', @admin_id, DATE_SUB(NOW(), INTERVAL 90 DAY), 1, DATE_SUB(NOW(), INTERVAL 100 DAY));
SET @deal_j = LAST_INSERT_ID();

-- ── 6. Commissions ──────────────────────────────────────────────

-- Caller commission — deal A (completed, PAID)
INSERT INTO commissions (deal_id, caller_id, amount, rate, role_type, is_paid, paid_at, paid_by, notes, created_at)
VALUES (@deal_a, @caller_id, 540.00, 12.00, 'caller', 1, DATE_SUB(NOW(), INTERVAL 30 DAY), @admin_id, 'Πληρωμή μέσω τραπεζικής μεταφοράς.', DATE_SUB(NOW(), INTERVAL 44 DAY));

-- Caller commission — deal F (completed, PAID)
INSERT INTO commissions (deal_id, caller_id, amount, rate, role_type, is_paid, paid_at, paid_by, notes, created_at)
VALUES (@deal_f, @caller_id, 336.00, 12.00, 'caller', 1, DATE_SUB(NOW(), INTERVAL 65 DAY), @admin_id, 'Πληρωμή μέσω τραπεζικής μεταφοράς.', DATE_SUB(NOW(), INTERVAL 68 DAY));

-- Caller commission — deal C (approved, UNPAID)
INSERT INTO commissions (deal_id, caller_id, amount, rate, role_type, is_paid, notes, created_at)
VALUES (@deal_c, @caller_id, 1440.00, 12.00, 'caller', 0, 'Αναμονή είσπραξης από πελάτη.', DATE_SUB(NOW(), INTERVAL 8 DAY));

-- Developer commission — deal A (completed, PAID)
INSERT INTO commissions (deal_id, caller_id, amount, rate, role_type, is_paid, paid_at, paid_by, notes, created_at)
VALUES (@deal_a, @dev_id, 900.00, 20.00, 'developer', 1, DATE_SUB(NOW(), INTERVAL 28 DAY), @admin_id, 'Ανάπτυξη eshop ολοκληρώθηκε.', DATE_SUB(NOW(), INTERVAL 44 DAY));

-- Developer commission — deal J (completed, PAID)
INSERT INTO commissions (deal_id, caller_id, amount, rate, role_type, is_paid, paid_at, paid_by, notes, created_at)
VALUES (@deal_j, @dev_id, 1100.00, 20.00, 'developer', 1, DATE_SUB(NOW(), INTERVAL 85 DAY), @admin_id, 'Mobile app ολοκλήρωση.', DATE_SUB(NOW(), INTERVAL 88 DAY));

-- Developer commission — deal B (in_progress, UNPAID)
INSERT INTO commissions (deal_id, caller_id, amount, rate, role_type, is_paid, notes, created_at)
VALUES (@deal_b, @dev_id, 1440.00, 20.00, 'developer', 0, 'Σε εξέλιξη. Θα πληρωθεί μετά παράδοση.', DATE_SUB(NOW(), INTERVAL 30 DAY));

-- Partner commission — deal G (completed, PAID)
INSERT INTO commissions (deal_id, caller_id, amount, rate, role_type, is_paid, paid_at, paid_by, notes, created_at)
VALUES (@deal_g, @partner_id, 1900.00, 20.00, 'partner', 1, DATE_SUB(NOW(), INTERVAL 50 DAY), @admin_id, 'Πλήρες κλείσιμο deal. Μέγιστη προμήθεια.', DATE_SUB(NOW(), INTERVAL 53 DAY));

-- Partner commission — deal H (approved, UNPAID)
INSERT INTO commissions (deal_id, caller_id, amount, rate, role_type, is_paid, notes, created_at)
VALUES (@deal_h, @partner_id, 720.00, 12.00, 'partner', 0, 'Αναμονή υπογραφής σύμβασης.', DATE_SUB(NOW(), INTERVAL 15 DAY));

-- ── 7. Projects ─────────────────────────────────────────────────

-- Project 1: linked to deal_a — COMPLETED
INSERT INTO projects (deal_id, developer_id, title, description, status, priority, start_date, deadline, actual_end, budget, tech_stack, repo_url, staging_url, live_url, created_at)
VALUES (@deal_a, @dev_id,
    'Eshop Acme Corporation',
    'Ανάπτυξη πλήρους eshop με WooCommerce, custom theme, ολοκλήρωση πληρωμών (Stripe/Viva), SEO βελτιστοποίηση.',
    'completed', 'high',
    DATE_SUB(NOW(), INTERVAL 55 DAY),
    DATE_SUB(NOW(), INTERVAL 30 DAY),
    DATE_SUB(NOW(), INTERVAL 32 DAY),
    4500.00, 'WordPress, WooCommerce, PHP, MySQL, Elementor',
    'https://github.com/softsystems/acme-eshop',
    'https://staging.acme.softsys.gr',
    'https://www.acmecorp.gr',
    DATE_SUB(NOW(), INTERVAL 56 DAY));
SET @proj1 = LAST_INSERT_ID();

-- Project 1 Phases
INSERT INTO project_phases (project_id, name, description, status, order_num, due_date, completed_at, created_at) VALUES
(@proj1, 'Ανάλυση Απαιτήσεων',   'Συλλογή specs, wireframes και χρωματική παλέτα.',         'completed', 1, DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 51 DAY), DATE_SUB(NOW(), INTERVAL 56 DAY)),
(@proj1, 'Σχεδιασμός UI/UX',      'Σχεδιασμός σελίδων (home, κατάστημα, προϊόν, checkout).', 'completed', 2, DATE_SUB(NOW(), INTERVAL 44 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 56 DAY)),
(@proj1, 'Ανάπτυξη Frontend',     'Υλοποίηση custom WooCommerce theme.',                     'completed', 3, DATE_SUB(NOW(), INTERVAL 38 DAY), DATE_SUB(NOW(), INTERVAL 38 DAY), DATE_SUB(NOW(), INTERVAL 56 DAY)),
(@proj1, 'Ολοκλήρωση Πληρωμών',  'Stripe & Viva Wallet integration.',                       'completed', 4, DATE_SUB(NOW(), INTERVAL 35 DAY), DATE_SUB(NOW(), INTERVAL 36 DAY), DATE_SUB(NOW(), INTERVAL 56 DAY)),
(@proj1, 'Testing & Παράδοση',    'QA testing, ταχύτητα, mobile responsive, παράδοση.',      'completed', 5, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 32 DAY), DATE_SUB(NOW(), INTERVAL 56 DAY));

-- Project 1 Notes
INSERT INTO project_notes (project_id, user_id, body, is_internal, created_at) VALUES
(@proj1, @dev_id,   'Ξεκίνησε η ανάπτυξη. Ο πελάτης έστειλε τα assets (logo, φωτογραφίες).', 0, DATE_SUB(NOW(), INTERVAL 53 DAY)),
(@proj1, @admin_id, 'Ο πελάτης ζήτησε αλλαγή στο χρωματολόγιο (navy αντί για γκρι).', 1, DATE_SUB(NOW(), INTERVAL 47 DAY)),
(@proj1, @dev_id,   'Το Stripe integration ολοκληρώθηκε. Δοκιμαστικές συναλλαγές OK.', 0, DATE_SUB(NOW(), INTERVAL 36 DAY)),
(@proj1, @dev_id,   'Παράδοση ολοκληρώθηκε. Ο πελάτης επιβεβαίωσε αποδοχή.', 0, DATE_SUB(NOW(), INTERVAL 32 DAY));

-- Project 2: linked to deal_b — IN PROGRESS
INSERT INTO projects (deal_id, developer_id, title, description, status, priority, start_date, deadline, budget, tech_stack, repo_url, staging_url, created_at)
VALUES (@deal_b, @dev_id,
    'CRM Gamma Services',
    'Προσαρμοσμένο CRM σύστημα με διαχείριση επαφών, pipeline πωλήσεων και αναφορές.',
    'in_progress', 'high',
    DATE_SUB(NOW(), INTERVAL 28 DAY),
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    7200.00, 'PHP, Laravel, MySQL, Vue.js, Chart.js',
    'https://github.com/softsystems/gamma-crm',
    'https://staging.gamma.softsys.gr',
    DATE_SUB(NOW(), INTERVAL 30 DAY));
SET @proj2 = LAST_INSERT_ID();

-- Project 2 Phases
INSERT INTO project_phases (project_id, name, description, status, order_num, due_date, completed_at, created_at) VALUES
(@proj2, 'Αρχιτεκτονική & DB Design', 'Σχεδιασμός βάσης δεδομένων και API endpoints.',       'completed',   1, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(@proj2, 'Backend API',               'Ανάπτυξη REST API με Laravel.',                       'completed',   2, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 11 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(@proj2, 'Frontend Dashboard',        'Vue.js dashboard με charts και pipeline view.',         'in_progress', 3, DATE_ADD(NOW(), INTERVAL 10 DAY), NULL,                            DATE_SUB(NOW(), INTERVAL 30 DAY)),
(@proj2, 'Reports & Exports',         'PDF / Excel εξαγωγή αναφορών.',                       'pending',     4, DATE_ADD(NOW(), INTERVAL 22 DAY), NULL,                            DATE_SUB(NOW(), INTERVAL 30 DAY)),
(@proj2, 'Testing & Go-Live',         'User acceptance testing και deployment.',               'pending',     5, DATE_ADD(NOW(), INTERVAL 30 DAY), NULL,                            DATE_SUB(NOW(), INTERVAL 30 DAY));

-- Project 2 Notes
INSERT INTO project_notes (project_id, user_id, body, is_internal, created_at) VALUES
(@proj2, @dev_id,   'Η βάση δεδομένων ολοκληρώθηκε. 18 tables, πλήρεις relations.', 0, DATE_SUB(NOW(), INTERVAL 21 DAY)),
(@proj2, @admin_id, 'Ο πελάτης ζήτησε επιπλέον module για διαχείριση τιμολογίων. Αξιολόγηση κόστους.', 1, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(@proj2, @dev_id,   'Backend API: 47 endpoints έτοιμα, tests πέρασαν 100%.', 0, DATE_SUB(NOW(), INTERVAL 11 DAY));

-- Project 3: linked to deal_j — COMPLETED
INSERT INTO projects (deal_id, developer_id, title, description, status, priority, start_date, deadline, actual_end, budget, tech_stack, live_url, created_at)
VALUES (@deal_j, @dev_id,
    'Mobile App — Delivery',
    'React Native εφαρμογή για παραγγελίες & delivery tracking.',
    'completed', 'urgent',
    DATE_SUB(NOW(), INTERVAL 95 DAY),
    DATE_SUB(NOW(), INTERVAL 75 DAY),
    DATE_SUB(NOW(), INTERVAL 78 DAY),
    5500.00, 'React Native, Node.js, MongoDB, Firebase',
    'https://play.google.com/store/apps/details?id=gr.softsys.delivery',
    DATE_SUB(NOW(), INTERVAL 96 DAY));
SET @proj3 = LAST_INSERT_ID();

-- Project 3 Phases
INSERT INTO project_phases (project_id, name, description, status, order_num, due_date, completed_at, created_at) VALUES
(@proj3, 'UI Design (Figma)',      'Σχεδιασμός κάθε οθόνης της εφαρμογής.',                'completed', 1, DATE_SUB(NOW(), INTERVAL 90 DAY), DATE_SUB(NOW(), INTERVAL 91 DAY), DATE_SUB(NOW(), INTERVAL 96 DAY)),
(@proj3, 'Core App Development',   'Βασική λειτουργικότητα: login, κατάλογος, καλάθι.',   'completed', 2, DATE_SUB(NOW(), INTERVAL 85 DAY), DATE_SUB(NOW(), INTERVAL 84 DAY), DATE_SUB(NOW(), INTERVAL 96 DAY)),
(@proj3, 'GPS & Live Tracking',    'Real-time tracking με Firebase.',                      'completed', 3, DATE_SUB(NOW(), INTERVAL 82 DAY), DATE_SUB(NOW(), INTERVAL 82 DAY), DATE_SUB(NOW(), INTERVAL 96 DAY)),
(@proj3, 'Push Notifications',     'FCM push notifications για orders.',                   'completed', 4, DATE_SUB(NOW(), INTERVAL 79 DAY), DATE_SUB(NOW(), INTERVAL 79 DAY), DATE_SUB(NOW(), INTERVAL 96 DAY)),
(@proj3, 'Store Submission',       'Google Play & App Store submission.',                  'completed', 5, DATE_SUB(NOW(), INTERVAL 75 DAY), DATE_SUB(NOW(), INTERVAL 78 DAY), DATE_SUB(NOW(), INTERVAL 96 DAY));

-- Project assignments (developer assigned to projects)
INSERT IGNORE INTO project_assignments (project_id, user_id, role_type, assigned_by, notes, assigned_at) VALUES
(@proj1, @dev_id, 'developer', @admin_id, 'Lead developer', DATE_SUB(NOW(), INTERVAL 56 DAY)),
(@proj2, @dev_id, 'developer', @admin_id, 'Lead developer', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(@proj3, @dev_id, 'developer', @admin_id, 'Lead developer', DATE_SUB(NOW(), INTERVAL 96 DAY));

-- ── 8. Messages ─────────────────────────────────────────────────
INSERT INTO messages (sender_id, receiver_id, subject, body, is_read, created_at) VALUES
(@admin_id, @caller_id,  'Καλώς ήρθες στο σύστημα!', 'Γεια σου Γιώργο, καλώς ήρθες στο Partnership Portal της SoftSystems. Έχεις ανατεθεί σε 10 επιχειρήσεις. Ξεκίνα επικοινωνίες και υπόβαλε deals όταν βρεις ενδιαφέρον!', 0, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(@admin_id, @dev_id,     'Νέο project ανατέθηκε', 'Γεια Νίκο, έχεις νέο project: CRM Gamma Services. Ξεκίνα την ανάλυση απαιτήσεων και ενημέρωσε για τυχόν ερωτήσεις.', 0, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(@admin_id, @partner_id, 'Καλώς ήρθες — Συνεργάτης', 'Γεια Ελένη! Καλώς ήρθες στο δίκτυο συνεργατών SoftSystems. Η κατηγορία σου είναι Β (12% προμήθεια). Μπορείς να υποβάλεις παραπομπές από το dashboard σου.', 0, DATE_SUB(NOW(), INTERVAL 65 DAY)),
(@caller_id, @admin_id,  'Ερώτηση για deal', 'Γεια, θέλω να ρωτήσω για την κατάσταση του deal με την Gamma Services. Έχει εγκριθεί;', 1, DATE_SUB(NOW(), INTERVAL 38 DAY)),
(@partner_id, @admin_id, 'Παραπομπή — Custom Software', 'Γεια, υπέβαλα νέα παραπομπή για custom software. Η εταιρεία είναι πολύ ενδιαφέρουσα και έχουν budget. Σας ενημερώνω.', 1, DATE_SUB(NOW(), INTERVAL 63 DAY)),
(@admin_id, @partner_id, 'Εγκρίθηκε η παραπομπή σου!', 'Ελένη, η παραπομπή για την εταιρεία custom software εγκρίθηκε! Προχωρά σε in_progress. Η προμήθειά σου θα υπολογιστεί μετά την ολοκλήρωση.', 0, DATE_SUB(NOW(), INTERVAL 55 DAY));

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Test data inserted successfully!' AS status;
SELECT CONCAT('test-caller  ID: ', @caller_id)  AS info
UNION SELECT CONCAT('test-developer ID: ', @dev_id)
UNION SELECT CONCAT('test-partner ID: ', @partner_id);
