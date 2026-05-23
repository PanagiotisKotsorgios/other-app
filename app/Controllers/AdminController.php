<?php
// E:\call_center\app\Controllers\AdminController.php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{User, Business, Deal, Commission, Interaction, Message, Project, Expense, Category, UserNote, PartnerDocument};

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
        $db           = \Database::getInstance();

        $dealStats        = $dealModel->adminStats();
        $intStats         = $intModel->adminStats();
        $commStats        = $commModel->summaryStats();
        $expStats         = $expModel->summaryStats();
        $projStats        = $projModel->adminProjectStats();
        $cityStats        = $bizModel->statsPerCity();
        $catStats         = $bizModel->statsPerCategory();
        $ranking          = $userModel->rankingTable();
        $partnerRanking   = $userModel->partnerRankingTable();
        $revenueByMonth   = $dealModel->revenueByMonth(12);
        $activityChart    = $intModel->chartData(30);
        $owedCallers      = $commModel->owedPerCaller();
        $owedPerRole      = $commModel->owedPerRole();
        $topDeals         = $dealModel->topRevenueDeals(8);
        $upcomingDeadlines= $projModel->upcomingDeadlines(14);
        $unread           = $msgModel->unreadCount(Auth::id());

        // Counts
        $totalBiz        = $bizModel->count();
        $totalCallers    = $userModel->count("role = 'caller' AND is_active = 1");
        $totalDevelopers = $userModel->count("role = 'developer' AND is_active = 1");
        $totalPartners   = $userModel->count("role = 'partner' AND is_active = 1");
        $totalDeals      = $dealModel->count();
        $unassignedBiz   = $bizModel->count("id NOT IN (SELECT business_id FROM caller_assignments)");

        // Business status breakdown
        $stmt = $db->query("SELECT status, COUNT(*) AS cnt FROM businesses GROUP BY status ORDER BY cnt DESC");
        $bizByStatus = $stmt->fetchAll();

        // Recent deals (last 10)
        $stmt = $db->query("
            SELECT d.id, d.amount, d.status, d.created_at,
                   b.company_name, u.name AS caller_name,
                   COALESCE(s.name,'—') AS service_name
            FROM deals d
            JOIN businesses b ON b.id = d.business_id
            JOIN users u      ON u.id = d.caller_id
            LEFT JOIN services s ON s.id = d.service_id
            ORDER BY d.created_at DESC LIMIT 10
        ");
        $recentDeals = $stmt->fetchAll();

        // Interactions this month
        $stmt = $db->query("SELECT COUNT(*) FROM interactions WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
        $intThisMonth = (int)$stmt->fetchColumn();

        // Financial summary
        $totalRevenue    = (float)($dealStats['total_revenue']  ?? 0);
        $totalExpenses   = (float)($expStats['total_expenses']  ?? 0);
        $totalCommOwed   = (float)($commStats['owed']           ?? 0);
        $netProfit       = $totalRevenue - $totalExpenses - $totalCommOwed;

        $this->view('admin.dashboard', compact(
            'dealStats','intStats','commStats','expStats','projStats',
            'cityStats','catStats','ranking','partnerRanking',
            'revenueByMonth','activityChart','owedCallers','owedPerRole',
            'topDeals','recentDeals','upcomingDeadlines','bizByStatus',
            'unread','totalBiz','totalCallers','totalDevelopers','totalPartners',
            'totalDeals','unassignedBiz','intThisMonth',
            'totalRevenue','totalExpenses','totalCommOwed','netProfit'
        ) + ['title' => 'Dashboard']);
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
        $unread     = (new Message())->unreadCount(Auth::id());
        $categories = (new Category())->all();
        $notes      = (new UserNote())->forUser((int)$id);
        $this->view('admin.callers.edit', [
            'title'      => 'Edit Caller',
            'caller'     => $caller,
            'categories' => $categories,
            'notes'      => $notes,
            'unread'     => $unread,
        ]);
    }

    public function callerUpdate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'        => trim($_POST['name'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'phone'       => trim($_POST['phone'] ?? ''),
            'is_active'   => (int)($_POST['is_active'] ?? 1),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
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
        $this->redirect(APP_URL . '/admin/callers/' . $id . '/edit');
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
        $roles      = $model->getRoles((int)$id);
        $unread     = (new Message())->unreadCount(Auth::id());
        $categories = (new Category())->all();
        $notes      = (new UserNote())->forUser((int)$id);
        $this->view('admin.developers.edit', [
            'title'      => 'Edit Developer',
            'developer'  => $developer,
            'roles'      => $roles,
            'categories' => $categories,
            'notes'      => $notes,
            'unread'     => $unread,
        ]);
    }

    public function developerUpdate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'        => trim($_POST['name'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'phone'       => trim($_POST['phone'] ?? ''),
            'is_active'   => (int)($_POST['is_active'] ?? 1),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
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

        $roles = $_POST['roles'] ?? ['developer'];
        $model->syncRoles((int)$id, $roles);

        Session::flash('success', 'Developer updated.');
        $this->redirect(APP_URL . '/admin/developers/' . $id . '/edit');
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
        $unread     = (new Message())->unreadCount(Auth::id());
        $categories = (new Category())->all();
        $this->view('admin.partners.create', [
            'title'      => 'Προσθήκη Συνεργάτη',
            'categories' => $categories,
            'unread'     => $unread,
        ]);
    }

    public function partnerStore(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'        => trim($_POST['name'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'password'    => $_POST['password'] ?? '',
            'phone'       => trim($_POST['phone'] ?? ''),
            'role'        => 'partner',
            'is_active'   => 1,
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
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

        // Always add partner role; add any extra roles selected
        $roles = ['partner'];
        foreach ($_POST['extra_roles'] ?? [] as $r) {
            if (in_array($r, ['developer', 'caller', 'admin'])) {
                $roles[] = $r;
            }
        }
        $model->syncRoles($id, array_unique($roles));

        Session::flash('success', 'Ο συνεργάτης δημιουργήθηκε επιτυχώς.');
        $this->redirect(APP_URL . '/admin/partners/' . $id . '/edit');
    }

    public function partnerEdit(string $id): void
    {
        Auth::requireAdmin();
        $model   = new User();
        $partner = $model->find((int)$id);
        if (!$partner) { $this->redirect(APP_URL . '/admin/partners'); return; }
        $roles      = $model->getRoles((int)$id);
        $unread     = (new Message())->unreadCount(Auth::id());
        $categories = (new Category())->all();
        $notes      = (new UserNote())->forUser((int)$id);
        $partnerDocs= (new PartnerDocument())->forPartner((int)$id);
        $this->view('admin.partners.edit', [
            'title'       => 'Επεξεργασία Συνεργάτη',
            'partner'     => $partner,
            'roles'       => $roles,
            'categories'  => $categories,
            'notes'       => $notes,
            'partnerDocs' => $partnerDocs,
            'unread'      => $unread,
        ]);
    }

    public function partnerUpdate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = [
            'name'        => trim($_POST['name'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'phone'       => trim($_POST['phone'] ?? ''),
            'is_active'   => (int)($_POST['is_active'] ?? 1),
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
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
        $this->redirect(APP_URL . '/admin/partners/' . $id . '/edit');
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

    // ── User Notes ─────────────────────────────────────────────────
    public function addUserNote(string $userId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $body     = trim($_POST['body'] ?? '');
        $isPinned = !empty($_POST['is_pinned']);

        if ($body !== '') {
            (new UserNote())->add((int)$userId, Auth::id(), $body, $isPinned);
            Session::flash('success', 'Note added.');
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin/callers');
    }

    public function deleteUserNote(string $noteId): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $model = new UserNote();
        $note  = $model->find((int)$noteId);
        if ($note) {
            $model->delete((int)$noteId);
            Session::flash('success', 'Note deleted.');
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? APP_URL . '/admin/callers');
    }

    private function bulkDeleteUsers(array $ids, string $redirectUrl): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) { $this->redirect($redirectUrl); return; }

        $ph = implode(',', array_fill(0, count($ids), '?'));
        $db = \Database::getInstance();
        $db->prepare("DELETE FROM commissions WHERE caller_id IN ($ph)")->execute($ids);
        $db->prepare("DELETE FROM deals       WHERE caller_id IN ($ph)")->execute($ids);
        $db->prepare("DELETE FROM users       WHERE id         IN ($ph)")->execute($ids);

        Session::flash('success', count($ids) . ' εγγραφές διαγράφηκαν.');
        $this->redirect($redirectUrl);
    }

    public function bulkDeleteCallers(): void
    {
        $this->bulkDeleteUsers(
            (array)($_POST['ids'] ?? []),
            APP_URL . '/admin/callers'
        );
    }

    public function bulkDeleteDevelopers(): void
    {
        $this->bulkDeleteUsers(
            (array)($_POST['ids'] ?? []),
            APP_URL . '/admin/developers'
        );
    }

    public function bulkDeletePartners(): void
    {
        $this->bulkDeleteUsers(
            (array)($_POST['ids'] ?? []),
            APP_URL . '/admin/partners'
        );
    }
}
