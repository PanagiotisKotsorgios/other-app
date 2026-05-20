<?php
// E:\call_center\app\Controllers\AdminController.php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{User, Business, Deal, Commission, Interaction, Message, Project, Expense};

class AdminController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireAdmin();

        $userModel    = new User();
        $bizModel     = new Business();
        $dealModel    = new Deal();
        $commModel    = new Commission();
        $intModel     = new Interaction();
        $msgModel     = new Message();
        $expModel     = new Expense();
        $projModel    = new Project();

        $dealStats    = $dealModel->adminStats();
        $intStats     = $intModel->adminStats();
        $commStats    = $commModel->summaryStats();
        $expStats     = $expModel->summaryStats();
        $projStats    = $projModel->adminProjectStats();
        $cityStats    = $bizModel->statsPerCity();
        $catStats     = $bizModel->statsPerCategory();
        $ranking      = $userModel->rankingTable();
        $revenueChart = $dealModel->revenueChart(6);
        $activityChart= $intModel->chartData(30);
        $owedCallers  = $commModel->owedPerCaller();
        $unread       = $msgModel->unreadCount(Auth::id());

        $totalBiz     = $bizModel->count();
        $totalCallers = $userModel->count("role = 'caller' AND is_active = 1");

        // Financial summary
        $totalRevenue    = (float)($dealStats['total_revenue']  ?? 0);
        $totalExpenses   = (float)($expStats['total_expenses']  ?? 0);
        $totalCommOwed   = (float)($commStats['owed']           ?? 0);
        $netProfit       = $totalRevenue - $totalExpenses - $totalCommOwed;

        $this->view('admin.dashboard', compact(
            'dealStats', 'intStats', 'commStats', 'expStats', 'projStats',
            'cityStats', 'catStats', 'ranking', 'revenueChart', 'activityChart',
            'owedCallers', 'unread', 'totalBiz', 'totalCallers',
            'totalRevenue', 'totalExpenses', 'totalCommOwed', 'netProfit'
        ) + ['title' => 'Admin Dashboard']);
    }

    // ── Callers CRUD ───────────────────────────────────────────────
    public function callers(): void
    {
        Auth::requireAdmin();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $model  = new User();
        $result = $model->callersPaginated($page, 20, $search);
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.callers.index', $result + ['title' => 'Callers', 'search' => $search, 'unread' => $unread]);
    }

    public function callerCreate(): void
    {
        Auth::requireAdmin();
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.callers.create', ['title' => 'Add Caller', 'unread' => $unread]);
    }

    public function callerStore(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'      => trim($_POST['name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'password'  => $_POST['password'] ?? '',
            'phone'     => trim($_POST['phone'] ?? ''),
            'role'      => 'caller',
            'is_active' => 1,
        ];
        $errors = $this->validate($data, [
            'name'     => 'required|max:120',
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/callers/create');
            return;
        }

        $model = new User();
        $id    = $model->createUser($data);
        $model->addRole($id, 'caller');
        Session::flash('success', 'Caller created successfully.');
        $this->redirect(APP_URL . '/admin/callers');
    }

    public function callerEdit(string $id): void
    {
        Auth::requireAdmin();
        $model  = new User();
        $caller = $model->find((int)$id);
        if (!$caller) { $this->redirect(APP_URL . '/admin/callers'); return; }
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.callers.edit', ['title' => 'Edit Caller', 'caller' => $caller, 'unread' => $unread]);
    }

    public function callerUpdate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'      => trim($_POST['name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'phone'     => trim($_POST['phone'] ?? ''),
            'is_active' => (int)($_POST['is_active'] ?? 1),
        ];
        $errors = $this->validate($data, ['name' => 'required', 'email' => 'required|email']);

        if ($errors) {
            Session::flash('errors', $errors);
            $this->redirect(APP_URL . '/admin/callers/' . $id . '/edit');
            return;
        }

        $model = new User();
        $model->update((int)$id, $data);

        if (!empty($_POST['password'])) {
            $model->updatePassword((int)$id, $_POST['password']);
        }

        Session::flash('success', 'Caller updated.');
        $this->redirect(APP_URL . '/admin/callers');
    }

    public function callerDelete(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        try {
            (new User())->delete((int)$id);
            Session::flash('success', 'Caller deleted.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect(APP_URL . '/admin/callers');
    }

    public function callerStats(string $id): void
    {
        Auth::requireAdmin();
        $userModel = new User();
        $caller    = $userModel->find((int)$id);
        if (!$caller) { $this->redirect(APP_URL . '/admin/callers'); return; }
        $stats  = $userModel->callerStats((int)$id);
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.callers.stats', ['title' => 'Caller Stats', 'caller' => $caller, 'stats' => $stats, 'unread' => $unread]);
    }

    // ── Developers CRUD ────────────────────────────────────────────
    public function developers(): void
    {
        Auth::requireAdmin();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $model  = new User();
        $result = $model->developersPaginated($page, 20, $search);

        // Attach stats to each developer
        $projModel = new Project();
        foreach ($result['data'] as &$dev) {
            $dev['stats'] = $projModel->developerStats($dev['id']);
        }
        unset($dev);

        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.developers.index', $result + [
            'title'  => 'Developers',
            'search' => $search,
            'unread' => $unread,
        ]);
    }

    public function developerCreate(): void
    {
        Auth::requireAdmin();
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.developers.create', ['title' => 'Add Developer', 'unread' => $unread]);
    }

    public function developerStore(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'      => trim($_POST['name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'password'  => $_POST['password'] ?? '',
            'phone'     => trim($_POST['phone'] ?? ''),
            'role'      => 'developer',
            'is_active' => 1,
        ];
        $errors = $this->validate($data, [
            'name'     => 'required|max:120',
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/developers/create');
            return;
        }

        $model = new User();
        $id    = $model->createUser($data);
        $model->addRole($id, 'developer');

        Session::flash('success', 'Developer created successfully.');
        $this->redirect(APP_URL . '/admin/developers');
    }

    public function developerEdit(string $id): void
    {
        Auth::requireAdmin();
        $model     = new User();
        $developer = $model->find((int)$id);
        if (!$developer) { $this->redirect(APP_URL . '/admin/developers'); return; }
        $roles  = $model->getRoles((int)$id);
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.developers.edit', [
            'title'     => 'Edit Developer',
            'developer' => $developer,
            'roles'     => $roles,
            'unread'    => $unread,
        ]);
    }

    public function developerUpdate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'      => trim($_POST['name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'phone'     => trim($_POST['phone'] ?? ''),
            'is_active' => (int)($_POST['is_active'] ?? 1),
        ];
        $errors = $this->validate($data, ['name' => 'required', 'email' => 'required|email']);

        if ($errors) {
            Session::flash('errors', $errors);
            $this->redirect(APP_URL . '/admin/developers/' . $id . '/edit');
            return;
        }

        $model = new User();
        $model->update((int)$id, $data);

        if (!empty($_POST['password'])) {
            $model->updatePassword((int)$id, $_POST['password']);
        }

        // Sync roles
        $roles = $_POST['roles'] ?? ['developer'];
        $model->syncRoles((int)$id, $roles);

        Session::flash('success', 'Developer updated.');
        $this->redirect(APP_URL . '/admin/developers');
    }

    public function developerDelete(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        try {
            (new User())->delete((int)$id);
            Session::flash('success', 'Developer deleted.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect(APP_URL . '/admin/developers');
    }

    // ── Partners CRUD ──────────────────────────────────────────────
    public function partners(): void
    {
        Auth::requireAdmin();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $model  = new User();
        $result = $model->partnersPaginated($page, 20, $search);

        foreach ($result['data'] as &$partner) {
            $partner['stats'] = $model->partnerStats($partner['id']);
        }
        unset($partner);

        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.partners.index', $result + [
            'title'  => 'Partners',
            'search' => $search,
            'unread' => $unread,
        ]);
    }

    public function partnerCreate(): void
    {
        Auth::requireAdmin();
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.partners.create', ['title' => 'Add Partner', 'unread' => $unread]);
    }

    public function partnerStore(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'      => trim($_POST['name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'password'  => $_POST['password'] ?? '',
            'phone'     => trim($_POST['phone'] ?? ''),
            'role'      => 'partner',
            'is_active' => 1,
        ];
        $errors = $this->validate($data, [
            'name'     => 'required|max:120',
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/partners/create');
            return;
        }

        $model = new User();
        $id    = $model->createUser($data);
        $model->addRole($id, 'partner');

        Session::flash('success', 'Partner created successfully.');
        $this->redirect(APP_URL . '/admin/partners');
    }

    public function partnerEdit(string $id): void
    {
        Auth::requireAdmin();
        $model   = new User();
        $partner = $model->find((int)$id);
        if (!$partner) { $this->redirect(APP_URL . '/admin/partners'); return; }
        $roles  = $model->getRoles((int)$id);
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.partners.edit', [
            'title'   => 'Edit Partner',
            'partner' => $partner,
            'roles'   => $roles,
            'unread'  => $unread,
        ]);
    }

    public function partnerUpdate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'      => trim($_POST['name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'phone'     => trim($_POST['phone'] ?? ''),
            'is_active' => (int)($_POST['is_active'] ?? 1),
        ];
        $errors = $this->validate($data, ['name' => 'required', 'email' => 'required|email']);

        if ($errors) {
            Session::flash('errors', $errors);
            $this->redirect(APP_URL . '/admin/partners/' . $id . '/edit');
            return;
        }

        $model = new User();
        $model->update((int)$id, $data);

        if (!empty($_POST['password'])) {
            $model->updatePassword((int)$id, $_POST['password']);
        }

        $roles = $_POST['roles'] ?? ['partner'];
        $model->syncRoles((int)$id, $roles);

        Session::flash('success', 'Partner updated.');
        $this->redirect(APP_URL . '/admin/partners');
    }

    public function partnerDelete(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        try {
            (new User())->delete((int)$id);
            Session::flash('success', 'Partner deleted.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect(APP_URL . '/admin/partners');
    }

    // ── Role Management ────────────────────────────────────────────
    public function assignRoles(string $userId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $roles = $_POST['roles'] ?? [];
        $valid = ['admin', 'caller', 'developer', 'partner'];
        $roles = array_filter($roles, fn($r) => in_array($r, $valid));

        $model = new User();
        $user  = $model->find((int)$userId);
        if (!$user) { $this->back(); return; }

        $model->syncRoles((int)$userId, array_values($roles));
        Session::flash('success', 'Roles updated for ' . $user['name'] . '.');
        $this->back();
    }
}
