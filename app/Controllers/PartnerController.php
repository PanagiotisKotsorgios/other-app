<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Deal, Commission, User, Business, Service, Message, Category, PartnerDocument};

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

        $validInvolvement = ['contact', 'full_closure'];
        $involvement = $_POST['partner_involvement'] ?? '';
        $dealData = [
            'business_id'         => $businessId,
            'caller_id'           => $admin['id'] ?? Auth::id(),
            'partner_id'          => Auth::id(),
            'service_id'          => !empty($_POST['service_id']) ? (int)$_POST['service_id'] : null,
            'amount'              => $amount,
            'currency'            => 'EUR',
            'notes'               => trim($_POST['notes'] ?? ''),
            'status'              => 'pending',
            'partner_involvement' => in_array($involvement, $validInvolvement) ? $involvement : 'contact',
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

    public function contract(): void
    {
        Auth::requirePartner();
        $model  = new PartnerDocument();
        $docs   = $model->forPartnerType(Auth::id(), 'contract');
        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('partner.contract', [
            'title'  => 'Σύμβαση Συνεργασίας',
            'docs'   => $docs,
            'unread' => $unread,
        ]);
    }

    public function invoices(): void
    {
        Auth::requirePartner();
        $model          = new PartnerDocument();
        $myInvoices     = $model->forPartnerType(Auth::id(), 'partner_invoice');
        $clientInvoices = $model->forPartnerType(Auth::id(), 'client_invoice');
        $unread         = (new Message())->unreadCount(Auth::id());

        $this->view('partner.invoices', [
            'title'          => 'Τιμολόγια',
            'myInvoices'     => $myInvoices,
            'clientInvoices' => $clientInvoices,
            'unread'         => $unread,
        ]);
    }

    public function uploadInvoice(): void
    {
        Auth::requirePartner();
        CSRF::check();

        if (empty($_FILES['invoice_file']['name'])) {
            Session::flash('error', 'Δεν επιλέχθηκε αρχείο.');
            $this->redirect(APP_URL . '/partner/invoices');
            return;
        }

        $file    = $_FILES['invoice_file'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed) || $file['size'] > UPLOAD_MAX_SIZE) {
            Session::flash('error', 'Μη έγκυρος τύπος ή μέγεθος αρχείου (PDF, DOC, DOCX, έως 10MB).');
            $this->redirect(APP_URL . '/partner/invoices');
            return;
        }

        $uploadDir = UPLOAD_PATH . '/partner_docs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = uniqid('pinv_') . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            Session::flash('error', 'Αποτυχία μεταφόρτωσης αρχείου.');
            $this->redirect(APP_URL . '/partner/invoices');
            return;
        }

        (new PartnerDocument())->create([
            'partner_id'    => Auth::id(),
            'doc_type'      => 'partner_invoice',
            'filename'      => $filename,
            'original_name' => basename($file['name']),
            'title'         => trim($_POST['title'] ?? ''),
            'amount'        => !empty($_POST['amount']) ? (float)$_POST['amount'] : null,
            'notes'         => trim($_POST['notes'] ?? ''),
            'uploaded_by'   => Auth::id(),
        ]);

        Session::flash('success', 'Το τιμολόγιό σας μεταφορτώθηκε με επιτυχία.');
        $this->redirect(APP_URL . '/partner/invoices');
    }

    public function downloadDocument(string $id): void
    {
        Auth::requireLogin();
        $model = new PartnerDocument();
        $doc   = $model->find((int)$id);

        if (!$doc) { $this->back(); return; }

        if (!\App\Core\Auth::isAdmin() && $doc['partner_id'] !== \App\Core\Auth::id()) {
            $this->redirect(APP_URL . '/partner/invoices');
            return;
        }

        $path = UPLOAD_PATH . '/partner_docs/' . $doc['filename'];
        if (!file_exists($path)) {
            Session::flash('error', 'Το αρχείο δεν βρέθηκε στον διακομιστή.');
            $this->back();
            return;
        }

        $mime = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf'
            ? 'application/pdf'
            : 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $doc['original_name'] . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private');
        readfile($path);
        exit;
    }

    public function deleteDocument(string $id): void
    {
        Auth::requireLogin();
        CSRF::check();

        $model = new PartnerDocument();
        $doc   = $model->find((int)$id);

        if (!$doc) { $this->back(); return; }

        if (!\App\Core\Auth::isAdmin() && $doc['partner_id'] !== \App\Core\Auth::id()) {
            $this->redirect(APP_URL . '/partner/invoices');
            return;
        }

        $path = UPLOAD_PATH . '/partner_docs/' . $doc['filename'];
        if (file_exists($path)) unlink($path);

        $model->delete((int)$id);
        Session::flash('success', 'Το έγγραφο διαγράφηκε.');

        $redirect = $doc['doc_type'] === 'contract'
            ? APP_URL . '/partner/contract'
            : APP_URL . '/partner/invoices';
        $this->redirect($redirect);
    }
}
