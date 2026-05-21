<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Deal, Commission, User, Business, Service, Message, Category};

class PartnerController extends Controller
{
    public function dashboard(): void
    {
        Auth::requirePartner();

        $userModel   = new User();
        $commModel   = new Commission();
        $dealModel   = new Deal();

        $user        = $userModel->findWithCategory(Auth::id());
        $stats       = $userModel->partnerStats(Auth::id());
        $roles       = $userModel->getRoles(Auth::id());
        $isDeveloper = in_array('developer', $roles);
        $recentDeals = $dealModel->forPartner(Auth::id(), [], 1, 5);
        $commData    = $commModel->forUser(Auth::id(), 1, 5);
        $unread      = (new Message())->unreadCount(Auth::id());

        $this->view('partner.dashboard', [
            'title'       => 'Πίνακας Ελέγχου Συνεργάτη',
            'stats'       => $stats,
            'user'        => $user,
            'roles'       => $roles,
            'isDeveloper' => $isDeveloper,
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

        $commModel  = new Commission();
        $commData   = $commModel->forUser(Auth::id(), 1, 500);
        $commByDeal = [];
        foreach ($commData['data'] as $c) {
            if ($c['role_type'] === 'partner') {
                $commByDeal[$c['deal_id']] = $c;
            }
        }

        $user   = (new User())->findWithCategory(Auth::id());
        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('partner.referrals.index', $result + [
            'title'       => 'Οι Παραπομπές Μου',
            'filters'     => $filters,
            'commByDeal'  => $commByDeal,
            'partnerRate' => $user['partner_rate'] ?? 20,
            'unread'      => $unread,
        ]);
    }

    public function submitReferral(): void
    {
        Auth::requirePartner();
        CSRF::check();

        $businessId = !empty($_POST['business_id']) ? (int)$_POST['business_id'] : null;

        if (!$businessId) {
            $bizData = [
                'company_name' => trim($_POST['company_name'] ?? ''),
                'phone'        => trim($_POST['phone'] ?? ''),
                'email'        => trim($_POST['email'] ?? ''),
                'city'         => trim($_POST['city'] ?? ''),
                'status'       => 'new',
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
            Session::flash('error', 'Το ποσό της συμφωνίας πρέπει να είναι μεγαλύτερο από 0.');
            $this->redirect(APP_URL . '/partner/referrals');
            return;
        }

        $db        = \Database::getInstance();
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
        Session::flash('success', 'Η παραπομπή υποβλήθηκε! Ένας διαχειριστής θα την αξιολογήσει σύντομα.');
        $this->redirect(APP_URL . '/partner/referrals');
    }

    public function commissions(): void
    {
        Auth::requirePartner();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $model  = new Commission();
        $result = $model->forUser(Auth::id(), $page, 20);
        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('partner.commissions', $result + [
            'title'  => 'Οι Προμήθειές Μου',
            'unread' => $unread,
        ]);
    }
}
