<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Commission, Message};

class CommissionController extends Controller
{
    public function adminIndex(): void
    {
        Auth::requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'is_paid'   => $_GET['is_paid']   ?? '',
            'caller_id' => $_GET['caller_id'] ?? '',
        ];
        $model   = new Commission();
        $result  = $model->listAll($filters, $page, 20);
        $stats   = $model->summaryStats();
        $callers = (new \App\Models\User())->callers();
        $unread  = (new Message())->unreadCount(Auth::id());

        $this->view('admin.commissions.index', $result + [
            'title'   => 'Commissions',
            'filters' => $filters,
            'stats'   => $stats,
            'callers' => $callers,
            'unread'  => $unread,
        ]);
    }

    public function markPaid(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new Commission();
        $model->markPaid((int)$id, Auth::id());
        Session::flash('success', 'Commission marked as paid.');
        $this->redirect(APP_URL . '/admin/commissions');
    }

    public function markUnpaid(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new Commission();
        $model->update((int)$id, ['is_paid' => 0, 'paid_at' => null, 'paid_by' => null]);
        Session::flash('success', 'Commission marked as unpaid.');
        $this->redirect(APP_URL . '/admin/commissions');
    }

    public function callerIndex(): void
    {
        Auth::requireCaller();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $model  = new Commission();
        $result = $model->forCaller(Auth::id(), $page, 20);
        $unread = (new Message())->unreadCount(Auth::id());

        // Summary for caller
        $db     = \Database::getInstance();
        $stmt   = $db->prepare("SELECT COALESCE(SUM(amount),0) AS total, COALESCE(SUM(CASE WHEN is_paid=0 THEN amount ELSE 0 END),0) AS owed, COALESCE(SUM(CASE WHEN is_paid=1 THEN amount ELSE 0 END),0) AS paid FROM commissions WHERE caller_id=?");
        $stmt->execute([Auth::id()]);
        $summary = $stmt->fetch();

        $this->view('caller.commissions.index', $result + [
            'title'   => 'My Commissions',
            'summary' => $summary,
            'unread'  => $unread,
        ]);
    }
}
