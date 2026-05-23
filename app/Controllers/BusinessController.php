<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Business, User, Message};

class BusinessController extends Controller
{
    // ── Admin: list all businesses ──────────────────────────────────
    public function index(): void
    {
        Auth::requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'city'      => $_GET['city']      ?? '',
            'category'  => $_GET['category']  ?? '',
            'status'    => $_GET['status']     ?? '',
            'caller_id' => $_GET['caller_id']  ?? '',
            'search'    => $_GET['search']     ?? '',
        ];
        $bizModel = new Business();
        $result   = $bizModel->filter($filters, $page, 20);
        $callers  = (new User())->callers();
        $cities   = $bizModel->cities();
        $cats     = $bizModel->categories();
        $unread   = (new Message())->unreadCount(Auth::id());

        $this->view('admin.businesses.index', $result + [
            'title'   => 'Businesses',
            'filters' => $filters,
            'callers' => $callers,
            'cities'  => $cities,
            'cats'    => $cats,
            'unread'  => $unread,
        ]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $unread  = (new Message())->unreadCount(Auth::id());
        $callers = (new User())->callers();
        $this->view('admin.businesses.create', ['title' => 'Add Business', 'callers' => $callers, 'unread' => $unread]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'company_name' => trim($_POST['company_name'] ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'website'      => trim($_POST['website'] ?? ''),
            'address'      => trim($_POST['address'] ?? ''),
            'city'         => trim($_POST['city'] ?? ''),
            'country'      => trim($_POST['country'] ?? ''),
            'category'     => trim($_POST['category'] ?? ''),
            'notes'        => trim($_POST['notes'] ?? ''),
            'status'       => 'new',
            'created_by'   => Auth::id(),
        ];
        $errors = $this->validate($data, ['company_name' => 'required|max:200']);

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/businesses/create');
            return;
        }

        $model  = new Business();
        $bid    = $model->create($data);

        if (!empty($_POST['caller_id'])) {
            $model->bulkAssign([$bid], (int)$_POST['caller_id'], Auth::id());
        }

        Session::flash('success', 'Business created.');
        $this->redirect(APP_URL . '/admin/businesses');
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $model    = new Business();
        $business = $model->find((int)$id);
        if (!$business) { $this->redirect(APP_URL . '/admin/businesses'); return; }
        $callers  = (new User())->callers();
        $unread   = (new Message())->unreadCount(Auth::id());
        $this->view('admin.businesses.edit', ['title' => 'Edit Business', 'business' => $business, 'callers' => $callers, 'unread' => $unread]);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data = [
            'company_name' => trim($_POST['company_name'] ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'website'      => trim($_POST['website'] ?? ''),
            'address'      => trim($_POST['address'] ?? ''),
            'city'         => trim($_POST['city'] ?? ''),
            'country'      => trim($_POST['country'] ?? ''),
            'category'     => trim($_POST['category'] ?? ''),
            'notes'        => trim($_POST['notes'] ?? ''),
            'status'       => $_POST['status'] ?? 'new',
        ];

        $model = new Business();
        $model->update((int)$id, $data);

        if (!empty($_POST['caller_id'])) {
            \Database::getInstance()->prepare("DELETE FROM caller_assignments WHERE business_id=?")->execute([(int)$id]);
            $model->bulkAssign([(int)$id], (int)$_POST['caller_id'], Auth::id());
        }

        Session::flash('success', 'Business updated.');
        $this->redirect(APP_URL . '/admin/businesses');
    }

    public function delete(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        try {
            (new Business())->delete((int)$id);
            Session::flash('success', 'Business deleted.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect(APP_URL . '/admin/businesses');
    }

    public function bulkAssign(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $callerId = (int)($_POST['caller_id'] ?? 0);
        $ids      = array_map('intval', (array)($_POST['business_ids'] ?? []));
        $mode     = $_POST['assign_mode'] ?? 'manual';

        $model = new Business();

        if ($mode === 'random') {
            $qty  = (int)($_POST['random_qty'] ?? 10);
            $done = $model->randomAssign($callerId, $qty, Auth::id());
        } else {
            $done = $model->bulkAssign($ids, $callerId, Auth::id());
        }

        Session::flash('success', "{$done} business(es) assigned.");
        $this->redirect(APP_URL . '/admin/businesses');
    }

    // ── Caller: assigned businesses ─────────────────────────────────
    public function myBusinesses(): void
    {
        Auth::requireCaller();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];
        $model  = new Business();
        $result = $model->assignedToCaller(Auth::id(), $filters, $page, 20);
        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('caller.businesses.index', $result + [
            'title'   => 'My Businesses',
            'filters' => $filters,
            'unread'  => $unread,
        ]);
    }

    public function callerCreate(): void
    {
        Auth::requireCaller();
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('caller.businesses.create', ['title' => 'Νέο Lead', 'unread' => $unread]);
    }

    public function callerStore(): void
    {
        Auth::requireCaller();
        CSRF::check();

        $data = [
            'company_name' => trim($_POST['company_name'] ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'website'      => trim($_POST['website'] ?? ''),
            'address'      => trim($_POST['address'] ?? ''),
            'city'         => trim($_POST['city'] ?? ''),
            'country'      => trim($_POST['country'] ?? ''),
            'category'     => trim($_POST['category'] ?? ''),
            'notes'        => trim($_POST['notes'] ?? ''),
            'status'       => 'new',
            'created_by'   => Auth::id(),
        ];

        $errors = $this->validate($data, ['company_name' => 'required|max:200']);
        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/caller/businesses/create');
            return;
        }

        $model = new Business();
        $bid   = $model->create($data);
        $model->bulkAssign([$bid], Auth::id(), Auth::id());

        Session::flash('success', 'Το lead καταχωρήθηκε επιτυχώς.');
        $this->redirect(APP_URL . '/caller/businesses/' . $bid);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $model    = new Business();
        $business = $model->find((int)$id);
        if (!$business) { $this->redirect(APP_URL . (Auth::isAdmin() ? '/admin/businesses' : '/caller/businesses')); return; }

        $services = (new \App\Models\Service())->all();
        $intModel = new \App\Models\Interaction();
        $interactions = $intModel->forBusiness((int)$id, Auth::isAdmin() ? 0 : Auth::id());
        $unread   = (new Message())->unreadCount(Auth::id());

        $this->view(Auth::isAdmin() ? 'admin.businesses.show' : 'caller.businesses.show', [
            'title'        => $business['company_name'],
            'business'     => $business,
            'interactions' => $interactions,
            'services'     => $services,
            'unread'       => $unread,
        ]);
    }
}
