<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Deal, Business, Service, Message, Commission};

class DealController extends Controller
{
    // ── Caller: list own deals ──────────────────────────────────────
    public function myDeals(): void
    {
        Auth::requireCaller();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = ['status' => $_GET['status'] ?? '', 'search' => $_GET['search'] ?? ''];
        $model   = new Deal();
        $result  = $model->forCaller(Auth::id(), $filters, $page, 20);
        $unread  = (new Message())->unreadCount(Auth::id());

        $this->view('caller.deals.index', $result + [
            'title'   => 'My Deals',
            'filters' => $filters,
            'unread'  => $unread,
        ]);
    }

    public function createForm(string $businessId): void
    {
        Auth::requireCaller();
        $business = (new Business())->find((int)$businessId);
        if (!$business) { $this->redirect(APP_URL . '/caller/businesses'); return; }

        $services = (new Service())->all();
        $unread   = (new Message())->unreadCount(Auth::id());

        $this->view('caller.deals.create', [
            'title'    => 'Submit Deal',
            'business' => $business,
            'services' => $services,
            'unread'   => $unread,
        ]);
    }

    public function store(): void
    {
        Auth::requireCaller();
        CSRF::check();

        $data = [
            'business_id' => (int)($_POST['business_id'] ?? 0),
            'caller_id'   => Auth::id(),
            'service_id'  => !empty($_POST['service_id']) ? (int)$_POST['service_id'] : null,
            'amount'      => (float)($_POST['amount'] ?? 0),
            'currency'    => 'EUR',
            'notes'       => trim($_POST['notes'] ?? ''),
            'status'      => 'pending',
        ];

        $errors = $this->validate($data, [
            'business_id' => 'required',
            'amount'      => 'required|numeric',
        ]);

        if ($data['amount'] <= 0) $errors['amount'] = 'Amount must be greater than 0.';

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/caller/deals/create/' . $data['business_id']);
            return;
        }

        // Handle proposal file
        if (!empty($_FILES['proposal_file']['name'])) {
            $file = $_FILES['proposal_file'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'doc', 'docx']) && $file['size'] <= UPLOAD_MAX_SIZE) {
                $filename = uniqid('deal_') . '.' . $ext;
                move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/proposals/' . $filename);
                $data['proposal_file'] = $filename;
            }
        }

        $model = new Deal();
        $id    = $model->create($data);

        // Update business status
        (new Business())->update($data['business_id'], ['status' => 'deal_closed']);

        Session::flash('success', 'Deal submitted. Awaiting admin approval.');
        $this->redirect(APP_URL . '/caller/deals');
    }

    // ── Admin: list all deals ───────────────────────────────────────
    public function adminIndex(): void
    {
        Auth::requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'    => $_GET['status']    ?? '',
            'caller_id' => $_GET['caller_id'] ?? '',
            'search'    => $_GET['search']    ?? '',
        ];

        $model   = new Deal();
        $result  = $model->listAll($filters, $page, 20);
        $callers = (new \App\Models\User())->callers();
        $unread  = (new Message())->unreadCount(Auth::id());

        $this->view('admin.deals.index', $result + [
            'title'   => 'Deals',
            'filters' => $filters,
            'callers' => $callers,
            'unread'  => $unread,
        ]);
    }

    public function adminShow(string $id): void
    {
        Auth::requireAdmin();
        $deal   = (new Deal())->withDetails((int)$id);
        if (!$deal) { $this->redirect(APP_URL . '/admin/deals'); return; }
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.deals.show', ['title' => 'Deal #' . $id, 'deal' => $deal, 'unread' => $unread]);
    }

    public function approve(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new Deal();
        $model->approve((int)$id, Auth::id());
        Session::flash('success', 'Deal approved and commission created.');
        $this->redirect(APP_URL . '/admin/deals/' . $id);
    }

    public function reject(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new Deal();
        $model->update((int)$id, ['status' => 'rejected']);
        Session::flash('success', 'Deal rejected.');
        $this->redirect(APP_URL . '/admin/deals/' . $id);
    }

    public function setInProgress(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new Deal();
        $deal  = $model->find((int)$id);
        if ($deal && $deal['status'] === 'approved') {
            $model->update((int)$id, ['status' => 'in_progress']);
            Session::flash('success', 'Deal marked as In Progress.');
        }
        $this->redirect(APP_URL . '/admin/deals/' . $id);
    }

    public function setCompleted(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new Deal();
        $model->update((int)$id, ['status' => 'completed']);
        Session::flash('success', 'Deal marked as Completed.');
        $this->redirect(APP_URL . '/admin/deals/' . $id);
    }

    public function signContract(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new Deal();
        $model->signContract((int)$id);
        Session::flash('success', 'Contract signed and project created.');
        $this->redirect(APP_URL . '/admin/deals/' . $id);
    }

    public function assignDeveloper(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $developerId = (int)($_POST['developer_id'] ?? 0);
        $model       = new Deal();
        $model->assignDeveloper((int)$id, $developerId);
        Session::flash('success', 'Developer assigned.');
        $this->redirect(APP_URL . '/admin/deals/' . $id);
    }

    public function assignPartner(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $partnerId = (int)($_POST['partner_id'] ?? 0);
        $model     = new Deal();
        $model->assignPartner((int)$id, $partnerId);
        Session::flash('success', 'Partner assigned.');
        $this->redirect(APP_URL . '/admin/deals/' . $id);
    }

    public function bulkDelete(): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $ids = array_values(array_filter(array_map('intval', (array)($_POST['ids'] ?? []))));
        if (empty($ids)) { $this->redirect(APP_URL . '/admin/deals'); return; }

        $ph = implode(',', array_fill(0, count($ids), '?'));
        \Database::getInstance()->prepare("DELETE FROM deals WHERE id IN ($ph)")->execute($ids);

        Session::flash('success', count($ids) . ' συμφωνίες διαγράφηκαν.');
        $this->redirect(APP_URL . '/admin/deals');
    }
}
