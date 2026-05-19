<?php
// E:\call_center\app\Controllers\FinancialController.php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Deal, Commission, Expense, Invoice, User, Message, Project};

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
            // Delete receipt file if any
            if ($expense['receipt_file']) {
                $path = UPLOAD_PATH . '/receipts/' . $expense['receipt_file'];
                if (file_exists($path)) unlink($path);
            }
            $model->delete((int)$id);
            Session::flash('success', 'Expense deleted.');
        }
        $this->redirect(APP_URL . '/admin/financials/expenses');
    }
}
