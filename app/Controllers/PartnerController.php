<?php
// E:\call_center\app\Controllers\PartnerController.php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Deal, Commission, User, Business, Service, Message};

class PartnerController extends Controller
{
    public function dashboard(): void
    {
        Auth::requirePartner();

        $userModel = new User();
        $commModel = new Commission();
        $dealModel = new Deal();

        $stats       = $userModel->partnerStats(Auth::id());
        $recentDeals = $dealModel->forPartner(Auth::id(), [], 1, 5);
        $commData    = $commModel->forPartner(Auth::id(), 1, 5);
        $unread      = (new Message())->unreadCount(Auth::id());

        $this->view('partner.dashboard', [
            'title'       => 'Partner Dashboard',
            'stats'       => $stats,
            'recentDeals' => $recentDeals['data'],
            'commData'    => $commData['data'],
            'unread'      => $unread,
        ]);
    }

    public function referrals(): void
    {
        Auth::requirePartner();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = ['status' => $_GET['status'] ?? ''];
        $model   = new Deal();
        $result  = $model->forPartner(Auth::id(), $filters, $page, 20);

        // Commission breakdown
        $commModel = new Commission();
        $commData  = $commModel->forPartner(Auth::id(), 1, 100);
        // Index by deal_id for quick lookup
        $commByDeal = [];
        foreach ($commData['data'] as $c) {
            $commByDeal[$c['deal_id']] = $c;
        }

        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('partner.referrals.index', $result + [
            'title'       => 'My Referrals',
            'filters'     => $filters,
            'commByDeal'  => $commByDeal,
            'unread'      => $unread,
        ]);
    }

    public function submitReferral(): void
    {
        Auth::requirePartner();
        CSRF::check();

        // Find or create business
        $businessId = !empty($_POST['business_id']) ? (int)$_POST['business_id'] : null;

        if (!$businessId) {
            // Create a minimal business record
            $bizData = [
                'company_name' => trim($_POST['company_name'] ?? ''),
                'phone'        => trim($_POST['phone'] ?? ''),
                'email'        => trim($_POST['email'] ?? ''),
                'city'         => trim($_POST['city'] ?? ''),
                'status'       => 'pending',
            ];
            $errors = $this->validate($bizData, ['company_name' => 'required|max:255']);
            if ($errors) {
                Session::flash('errors', $errors);
                $this->redirect(APP_URL . '/partner/referrals');
                return;
            }
            $businessId = (new Business())->create($bizData);
        }

        $amount = (float)($_POST['amount'] ?? 0);
        if ($amount <= 0) {
            Session::flash('error', 'Deal amount must be greater than 0.');
            $this->redirect(APP_URL . '/partner/referrals');
            return;
        }

        // Partner submits referral as a deal; caller_id points to themselves or an admin
        // Use the first admin as caller fallback
        $db       = \Database::getInstance();
        $adminStmt = $db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $admin     = $adminStmt->fetch();

        $dealData = [
            'business_id' => $businessId,
            'caller_id'   => $admin['id'] ?? Auth::id(),
            'partner_id'  => Auth::id(),
            'service_id'  => !empty($_POST['service_id']) ? (int)$_POST['service_id'] : null,
            'amount'      => $amount,
            'currency'    => 'EUR',
            'notes'       => trim($_POST['notes'] ?? ''),
            'status'      => 'pending',
        ];

        (new Deal())->create($dealData);
        Session::flash('success', 'Referral submitted! An admin will review it shortly.');
        $this->redirect(APP_URL . '/partner/referrals');
    }

    public function commissions(): void
    {
        Auth::requirePartner();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $model  = new Commission();
        $result = $model->forPartner(Auth::id(), $page, 20);
        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('partner.commissions', $result + [
            'title'  => 'My Commissions',
            'unread' => $unread,
        ]);
    }
}
