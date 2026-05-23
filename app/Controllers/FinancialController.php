<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Deal, Commission, Expense, Invoice, User, Message, Project};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment, Border, Font};
use Dompdf\Dompdf;
use Dompdf\Options;

class FinancialController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireAdmin();

        $dealModel    = new Deal();
        $commModel    = new Commission();
        $expModel     = new Expense();
        $invModel     = new Invoice();
        $userModel    = new User();
        $projModel    = new Project();

        $dealStats    = $dealModel->adminStats();
        $commStats    = $commModel->summaryStats();
        $expStats     = $expModel->summaryStats();
        $invStats     = $invModel->totalStats();
        $revenueChart = $dealModel->revenueByMonth(12);
        $expByCategory = $expModel->totalByCategory();
        $topDeals     = $dealModel->topRevenueDeals(10);
        $perProject   = $dealModel->perProjectFinancials();
        $callerEarnings   = $commModel->owedPerCaller();
        $developerEarnings = $commModel->earningsPerDeveloper();
        $partnerEarnings  = $commModel->earningsPerPartner();
        $recentExpenses   = $expModel->listAll([], 1, 10);
        $commByRole   = $commModel->owedPerRole();
        $upcomingDeadlines = $projModel->upcomingDeadlines(14);
        $unread       = (new Message())->unreadCount(Auth::id());

        $totalRevenue   = (float)($dealStats['total_revenue'] ?? 0);
        $totalExpenses  = (float)($expStats['total_expenses'] ?? 0);
        $totalComm      = (float)($commStats['owed'] ?? 0);
        $netProfit      = $totalRevenue - $totalExpenses - $totalComm;

        $this->view('admin.financial.index', [
            'title'             => 'Financial Dashboard',
            'dealStats'         => $dealStats,
            'commStats'         => $commStats,
            'expStats'          => $expStats,
            'invStats'          => $invStats,
            'revenueChart'      => $revenueChart,
            'expByCategory'     => $expByCategory,
            'topDeals'          => $topDeals,
            'perProject'        => $perProject,
            'callerEarnings'    => $callerEarnings,
            'developerEarnings' => $developerEarnings,
            'partnerEarnings'   => $partnerEarnings,
            'recentExpenses'    => $recentExpenses['data'],
            'commByRole'        => $commByRole,
            'upcomingDeadlines' => $upcomingDeadlines,
            'totalRevenue'      => $totalRevenue,
            'totalExpenses'     => $totalExpenses,
            'totalComm'         => $totalComm,
            'netProfit'         => $netProfit,
            'unread'            => $unread,
        ]);
    }

    public function expenses(): void
    {
        Auth::requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'project_id' => $_GET['project_id'] ?? '',
            'deal_id'    => $_GET['deal_id']    ?? '',
            'category'   => $_GET['category']   ?? '',
            'search'     => $_GET['search']      ?? '',
        ];
        $model   = new Expense();
        $result  = $model->listAll($filters, $page, 20);
        $stats   = $model->summaryStats();
        $byCat   = $model->totalByCategory();
        $unread  = (new Message())->unreadCount(Auth::id());

        $this->view('admin.financial.expenses', $result + [
            'title'   => 'Expense Management',
            'filters' => $filters,
            'stats'   => $stats,
            'byCat'   => $byCat,
            'unread'  => $unread,
        ]);
    }

    public function storeExpense(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data = [
            'project_id'   => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'deal_id'      => !empty($_POST['deal_id'])    ? (int)$_POST['deal_id']    : null,
            'description'  => trim($_POST['description'] ?? ''),
            'amount'       => (float)($_POST['amount'] ?? 0),
            'category'     => $_POST['category'] ?? 'other',
            'expense_date' => $_POST['expense_date'] ?: date('Y-m-d'),
            'created_by'   => Auth::id(),
        ];

        $errors = $this->validate($data, [
            'description' => 'required|max:255',
            'amount'      => 'required|numeric',
        ]);
        if ($errors) {
            Session::flash('errors', $errors);
            $this->redirect(APP_URL . '/admin/financials/expenses');
            return;
        }

        // Handle receipt file
        if (!empty($_FILES['receipt_file']['name'])) {
            $file    = $_FILES['receipt_file'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            if (in_array($ext, $allowed) && $file['size'] <= UPLOAD_MAX_SIZE) {
                $uploadDir = UPLOAD_PATH . '/receipts/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('rcpt_') . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $data['receipt_file'] = $filename;
                }
            }
        }

        (new Expense())->create($data);
        Session::flash('success', 'Expense recorded.');
        $this->redirect(APP_URL . '/admin/financials/expenses');
    }

    public function updateExpense(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $expense = (new Expense())->find((int)$id);
        if (!$expense) { $this->back(); return; }

        $data = [
            'description'  => trim($_POST['description'] ?? ''),
            'amount'       => (float)($_POST['amount'] ?? 0),
            'category'     => $_POST['category'] ?? 'other',
            'expense_date' => $_POST['expense_date'] ?: date('Y-m-d'),
        ];

        (new Expense())->update((int)$id, $data);
        Session::flash('success', 'Expense updated.');
        $this->redirect(APP_URL . '/admin/financials/expenses');
    }

    public function deleteExpense(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model   = new Expense();
        $expense = $model->find((int)$id);
        if ($expense) {
            if ($expense['receipt_file']) {
                $path = UPLOAD_PATH . '/receipts/' . $expense['receipt_file'];
                if (file_exists($path)) unlink($path);
            }
            $model->delete((int)$id);
            Session::flash('success', 'Expense deleted.');
        }
        $this->redirect(APP_URL . '/admin/financials/expenses');
    }

    public function exportExcel(): void
    {
        Auth::requireAdmin();

        $dealModel  = new Deal();
        $commModel  = new Commission();
        $expModel   = new Expense();

        $dealStats  = $dealModel->adminStats();
        $commStats  = $commModel->summaryStats();
        $expStats   = $expModel->summaryStats();
        $topDeals   = $dealModel->topRevenueDeals(100);
        $callerE    = $commModel->owedPerCaller();
        $developerE = $commModel->earningsPerDeveloper();
        $partnerE   = $commModel->earningsPerPartner();
        $expenses   = $expModel->listAll([], 1, 500)['data'];
        $perProject = $dealModel->perProjectFinancials();

        $totalRevenue  = (float)($dealStats['total_revenue'] ?? 0);
        $totalExpenses = (float)($expStats['total_expenses'] ?? 0);
        $totalComm     = (float)($commStats['owed'] ?? 0);
        $netProfit     = $totalRevenue - $totalExpenses - $totalComm;

        $sp = new Spreadsheet();
        $sp->getProperties()->setTitle('Financial Report')->setCreator('SoftSystems');

        // ── Helper closures ──────────────────────────────────────────
        $bold   = ['font' => ['bold' => true]];
        $header = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a56db']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $money  = '#,##0.00 €';
        $center = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];

        $autoWidth = function($sheet) {
            foreach ($sheet->getColumnIterator() as $col) {
                $sheet->getColumnDimension($col->getColumnIndex())->setAutoSize(true);
            }
        };

        // ── Sheet 1: Summary ────────────────────────────────────────
        $sum = $sp->getActiveSheet()->setTitle('Σύνοψη');
        $sum->getStyle('A1:B1')->applyFromArray($header);
        $sum->fromArray([['Δείκτης', 'Ποσό (€)']], null, 'A1');
        $rows = [
            ['Συνολικά Έσοδα',        $totalRevenue],
            ['Συνολικά Έξοδα',        $totalExpenses],
            ['Οφειλόμενες Προμήθειες', $totalComm],
            ['Καθαρό Κέρδος',          $netProfit],
            ['Συνολικές Συμφωνίες',   (int)($dealStats['total_deals'] ?? 0)],
            ['Ολοκληρωμένες',         (int)($dealStats['completed'] ?? 0)],
            ['Σε Εξέλιξη',            (int)($dealStats['in_progress'] ?? 0)],
        ];
        $sum->fromArray($rows, null, 'A2');
        foreach (range(2, 1 + count($rows)) as $r) {
            if ($r <= 5) {
                $sum->getStyle("B{$r}")->getNumberFormat()->setFormatCode($money);
            }
        }
        $sum->getStyle('A1:B1')->applyFromArray($header);
        $autoWidth($sum);

        // ── Sheet 2: Deals ──────────────────────────────────────────
        $sp->createSheet()->setTitle('Συμφωνίες');
        $ds = $sp->getSheetByName('Συμφωνίες');
        $dh = [['#','Επιχείρηση','Τηλεφωνητής','Κατάσταση','Ποσό','Προμήθειες','Έξοδα','Καθαρό','Ημ/νία']];
        $ds->fromArray($dh, null, 'A1');
        $ds->getStyle('A1:I1')->applyFromArray($header);
        $r = 2;
        foreach ($topDeals as $i => $d) {
            $net = $d['amount'] - $d['total_commissions'] - $d['total_expenses'];
            $ds->fromArray([
                [$i+1, $d['company_name'], $d['caller_name'], $d['status'],
                 $d['amount'], $d['total_commissions'], $d['total_expenses'], $net,
                 $d['created_at'] ? date('d/m/Y', strtotime($d['created_at'])) : '']
            ], null, "A{$r}");
            foreach (['E','F','G','H'] as $col) {
                $ds->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode($money);
            }
            $r++;
        }
        $autoWidth($ds);

        // ── Sheet 3: Commissions ─────────────────────────────────────
        $sp->createSheet()->setTitle('Προμήθειες');
        $cs = $sp->getSheetByName('Προμήθειες');
        $cs->fromArray([['Όνομα','Ρόλος','Σύνολο Κερδισμένων','Οφειλόμενο']], null, 'A1');
        $cs->getStyle('A1:D1')->applyFromArray($header);
        $r = 2;
        foreach ($callerE as $row) {
            $cs->fromArray([[$row['name'], 'Τηλεφωνητής', $row['owed'], $row['owed']]], null, "A{$r}");
            foreach (['C','D'] as $col) { $cs->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode($money); }
            $r++;
        }
        foreach ($developerE as $row) {
            $cs->fromArray([[$row['name'], 'Προγραμματιστής', $row['total_earned'], $row['owed']]], null, "A{$r}");
            foreach (['C','D'] as $col) { $cs->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode($money); }
            $r++;
        }
        foreach ($partnerE as $row) {
            $cs->fromArray([[$row['name'], 'Συνεργάτης', $row['total_earned'], $row['owed']]], null, "A{$r}");
            foreach (['C','D'] as $col) { $cs->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode($money); }
            $r++;
        }
        $autoWidth($cs);

        // ── Sheet 4: Expenses ────────────────────────────────────────
        $sp->createSheet()->setTitle('Έξοδα');
        $es = $sp->getSheetByName('Έξοδα');
        $es->fromArray([['Περιγραφή','Κατηγορία','Ποσό','Ημερομηνία','Δημιουργήθηκε Από']], null, 'A1');
        $es->getStyle('A1:E1')->applyFromArray($header);
        $r = 2;
        foreach ($expenses as $exp) {
            $es->fromArray([[
                $exp['description'], $exp['category'], $exp['amount'],
                $exp['expense_date'] ? date('d/m/Y', strtotime($exp['expense_date'])) : '',
                $exp['creator_name'] ?? '',
            ]], null, "A{$r}");
            $es->getStyle("C{$r}")->getNumberFormat()->setFormatCode($money);
            $r++;
        }
        $autoWidth($es);

        // ── Sheet 5: Per-Project ─────────────────────────────────────
        $sp->createSheet()->setTitle('Ανά Έργο');
        $ps = $sp->getSheetByName('Ανά Έργο');
        $ps->fromArray([['Επιχείρηση','Έργο','Ποσό Συμφωνίας','Προμήθειες','Έξοδα','Καθαρό']], null, 'A1');
        $ps->getStyle('A1:F1')->applyFromArray($header);
        $r = 2;
        foreach ($perProject as $row) {
            $net = $row['deal_amount'] - $row['commissions'] - $row['expenses'];
            $ps->fromArray([[
                $row['company_name'], $row['project_title'] ?? '',
                $row['deal_amount'], $row['commissions'], $row['expenses'], $net,
            ]], null, "A{$r}");
            foreach (['C','D','E','F'] as $col) {
                $ps->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode($money);
            }
            $r++;
        }
        $autoWidth($ps);

        $sp->setActiveSheetIndex(0);

        $filename = 'financial_report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($sp))->save('php://output');
        exit;
    }

    public function exportPdf(): void
    {
        Auth::requireAdmin();

        $dealModel  = new Deal();
        $commModel  = new Commission();
        $expModel   = new Expense();

        $dealStats  = $dealModel->adminStats();
        $commStats  = $commModel->summaryStats();
        $expStats   = $expModel->summaryStats();
        $topDeals   = $dealModel->topRevenueDeals(50);
        $callerE    = $commModel->owedPerCaller();
        $developerE = $commModel->earningsPerDeveloper();
        $partnerE   = $commModel->earningsPerPartner();
        $expenses   = $expModel->listAll([], 1, 100)['data'];
        $perProject = $dealModel->perProjectFinancials();

        $totalRevenue  = (float)($dealStats['total_revenue'] ?? 0);
        $totalExpenses = (float)($expStats['total_expenses'] ?? 0);
        $totalComm     = (float)($commStats['owed'] ?? 0);
        $netProfit     = $totalRevenue - $totalExpenses - $totalComm;
        $generated     = date('d/m/Y H:i');

        $fmt = fn($n) => '€' . number_format((float)$n, 2);

        ob_start();
        ?><!DOCTYPE html>
<html lang="el">
<head><meta charset="UTF-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#222;margin:20px}
h1{font-size:18px;color:#1a56db;margin-bottom:4px}
h2{font-size:13px;color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:3px;margin-top:20px}
.meta{font-size:10px;color:#666;margin-bottom:14px}
table{width:100%;border-collapse:collapse;margin-bottom:10px}
th{background:#1a56db;color:#fff;padding:5px 7px;text-align:left;font-size:10px}
td{padding:4px 7px;border-bottom:1px solid #e5e7eb;font-size:10px}
tr:nth-child(even) td{background:#f8fafc}
.kpi-row{display:table;width:100%;margin-bottom:14px}
.kpi{display:table-cell;width:25%;border:1px solid #dde;border-radius:5px;padding:8px 10px;text-align:center}
.kpi-val{font-size:16px;font-weight:bold;color:#1a56db}
.kpi-lbl{font-size:9px;color:#555}
.text-right{text-align:right}
.green{color:#198754}.red{color:#dc3545}.orange{color:#fd7e14}
</style>
</head>
<body>
<h1>Οικονομική Αναφορά — SoftSystems</h1>
<div class="meta">Δημιουργήθηκε: <?= $generated ?></div>

<h2>Σύνοψη</h2>
<table>
<tr><th>Δείκτης</th><th class="text-right">Ποσό</th></tr>
<tr><td>Συνολικά Έσοδα</td><td class="text-right green"><?= $fmt($totalRevenue) ?></td></tr>
<tr><td>Συνολικά Έξοδα</td><td class="text-right red"><?= $fmt($totalExpenses) ?></td></tr>
<tr><td>Οφειλόμενες Προμήθειες</td><td class="text-right orange"><?= $fmt($totalComm) ?></td></tr>
<tr><td><strong>Καθαρό Κέρδος</strong></td><td class="text-right <?= $netProfit >= 0 ? 'green' : 'red' ?>"><strong><?= $fmt($netProfit) ?></strong></td></tr>
<tr><td>Συνολικές Συμφωνίες</td><td class="text-right"><?= (int)($dealStats['total_deals'] ?? 0) ?></td></tr>
<tr><td>Ολοκληρωμένες Συμφωνίες</td><td class="text-right"><?= (int)($dealStats['completed'] ?? 0) ?></td></tr>
</table>

<h2>Κορυφαίες Συμφωνίες</h2>
<table>
<tr><th>#</th><th>Επιχείρηση</th><th>Τηλεφωνητής</th><th>Κατάσταση</th><th class="text-right">Ποσό</th><th class="text-right">Καθαρό</th></tr>
<?php foreach ($topDeals as $i => $d):
    $net = $d['amount'] - $d['total_commissions'] - $d['total_expenses'];
?>
<tr>
  <td><?= $i+1 ?></td>
  <td><?= htmlspecialchars($d['company_name']) ?></td>
  <td><?= htmlspecialchars($d['caller_name']) ?></td>
  <td><?= htmlspecialchars($d['status']) ?></td>
  <td class="text-right green"><?= $fmt($d['amount']) ?></td>
  <td class="text-right <?= $net >= 0 ? 'green' : 'red' ?>"><?= $fmt($net) ?></td>
</tr>
<?php endforeach ?>
<?php if(empty($topDeals)): ?><tr><td colspan="6" style="text-align:center;color:#888">Δεν υπάρχουν δεδομένα.</td></tr><?php endif ?>
</table>

<h2>Οικονομικά ανά Έργο</h2>
<table>
<tr><th>Επιχείρηση</th><th>Έργο</th><th class="text-right">Ποσό</th><th class="text-right">Προμήθειες</th><th class="text-right">Έξοδα</th><th class="text-right">Καθαρό</th></tr>
<?php foreach ($perProject as $row):
    $net = $row['deal_amount'] - $row['commissions'] - $row['expenses'];
?>
<tr>
  <td><?= htmlspecialchars($row['company_name']) ?></td>
  <td><?= htmlspecialchars($row['project_title'] ?? '—') ?></td>
  <td class="text-right"><?= $fmt($row['deal_amount']) ?></td>
  <td class="text-right orange"><?= $fmt($row['commissions']) ?></td>
  <td class="text-right red"><?= $fmt($row['expenses']) ?></td>
  <td class="text-right <?= $net >= 0 ? 'green' : 'red' ?>"><?= $fmt($net) ?></td>
</tr>
<?php endforeach ?>
<?php if(empty($perProject)): ?><tr><td colspan="6" style="text-align:center;color:#888">Δεν υπάρχουν δεδομένα.</td></tr><?php endif ?>
</table>

<h2>Προμήθειες ανά Άτομο</h2>
<table>
<tr><th>Όνομα</th><th>Ρόλος</th><th class="text-right">Κερδισμένα</th><th class="text-right">Οφειλόμενο</th></tr>
<?php foreach ($callerE as $row): ?>
<tr><td><?= htmlspecialchars($row['name']) ?></td><td>Τηλεφωνητής</td><td class="text-right"><?= $fmt($row['owed']) ?></td><td class="text-right orange"><?= $fmt($row['owed']) ?></td></tr>
<?php endforeach ?>
<?php foreach ($developerE as $row): ?>
<tr><td><?= htmlspecialchars($row['name']) ?></td><td>Προγραμματιστής</td><td class="text-right"><?= $fmt($row['total_earned']) ?></td><td class="text-right orange"><?= $fmt($row['owed']) ?></td></tr>
<?php endforeach ?>
<?php foreach ($partnerE as $row): ?>
<tr><td><?= htmlspecialchars($row['name']) ?></td><td>Συνεργάτης</td><td class="text-right"><?= $fmt($row['total_earned']) ?></td><td class="text-right orange"><?= $fmt($row['owed']) ?></td></tr>
<?php endforeach ?>
</table>

<h2>Πρόσφατα Έξοδα</h2>
<table>
<tr><th>Περιγραφή</th><th>Κατηγορία</th><th class="text-right">Ποσό</th><th>Ημερομηνία</th></tr>
<?php foreach ($expenses as $exp): ?>
<tr>
  <td><?= htmlspecialchars($exp['description']) ?></td>
  <td><?= htmlspecialchars($exp['category']) ?></td>
  <td class="text-right red"><?= $fmt($exp['amount']) ?></td>
  <td><?= $exp['expense_date'] ? date('d/m/Y', strtotime($exp['expense_date'])) : '—' ?></td>
</tr>
<?php endforeach ?>
<?php if(empty($expenses)): ?><tr><td colspan="4" style="text-align:center;color:#888">Δεν υπάρχουν έξοδα.</td></tr><?php endif ?>
</table>
</body></html>
<?php
        $html = ob_get_clean();

        $opts = new Options();
        $opts->set('isHtml5ParserEnabled', true);
        $opts->set('isRemoteEnabled', false);
        $opts->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($opts);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'financial_report_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}
