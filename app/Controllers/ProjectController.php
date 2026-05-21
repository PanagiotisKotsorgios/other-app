<?php
// E:\call_center\app\Controllers\ProjectController.php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Project, Deal, User, Message};

class ProjectController extends Controller
{
    // ── Admin: list all projects ──────────────────────────────────────────────
    public function adminIndex(): void
    {
        Auth::requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'       => $_GET['status']       ?? '',
            'developer_id' => $_GET['developer_id'] ?? '',
            'priority'     => $_GET['priority']     ?? '',
            'search'       => $_GET['search']       ?? '',
        ];
        $model      = new Project();
        $result     = $model->listAll($filters, $page, 20);
        $developers = (new User())->developers();
        $unread     = (new Message())->unreadCount(Auth::id());
        $stats      = $model->adminProjectStats();

        $this->view('admin.projects.index', $result + [
            'title'      => 'Projects',
            'filters'    => $filters,
            'developers' => $developers,
            'unread'     => $unread,
            'stats'      => $stats,
        ]);
    }

    // ── Admin: show project detail ────────────────────────────────────────────
    public function adminShow(string $id): void
    {
        Auth::requireAdmin();
        $model   = new Project();
        $project = $model->findWithDetails((int)$id);
        if (!$project) { $this->redirect(APP_URL . '/admin/projects'); return; }

        $phases      = $model->getPhases((int)$id);
        $notes       = $model->getNotes((int)$id, true);
        $developers  = (new User())->developers();
        $unread      = (new Message())->unreadCount(Auth::id());
        $assignments = $model->getAssignments((int)$id);
        $allUsers    = (new User())->allActive();

        // Contracts and invoices
        $contracts = (new \App\Models\Contract())->forDeal($project['deal_id']);
        $invoices  = (new \App\Models\Invoice())->forDeal($project['deal_id']);
        $expenses  = (new \App\Models\Expense())->forProject((int)$id);

        $this->view('admin.projects.show', [
            'title'       => $project['title'],
            'project'     => $project,
            'phases'      => $phases,
            'notes'       => $notes,
            'developers'  => $developers,
            'contracts'   => $contracts,
            'invoices'    => $invoices,
            'expenses'    => $expenses,
            'assignments' => $assignments,
            'allUsers'    => $allUsers,
            'unread'      => $unread,
        ]);
    }

    // ── Admin: create project from deal ──────────────────────────────────────
    public function create(string $dealId): void
    {
        Auth::requireAdmin();
        $deal = (new Deal())->withDetails((int)$dealId);
        if (!$deal) { $this->redirect(APP_URL . '/admin/deals'); return; }

        $developers = (new User())->developers();
        $unread     = (new Message())->unreadCount(Auth::id());

        $this->view('admin.projects.create', [
            'title'      => 'Create Project',
            'deal'       => $deal,
            'developers' => $developers,
            'unread'     => $unread,
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data = [
            'deal_id'      => (int)($_POST['deal_id'] ?? 0),
            'developer_id' => !empty($_POST['developer_id']) ? (int)$_POST['developer_id'] : null,
            'title'        => trim($_POST['title'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'status'       => 'awaiting_assignment',
            'priority'     => $_POST['priority'] ?? 'medium',
            'start_date'   => $_POST['start_date'] ?? null,
            'deadline'     => $_POST['deadline'] ?? null,
            'budget'       => (float)($_POST['budget'] ?? 0),
            'tech_stack'   => trim($_POST['tech_stack'] ?? ''),
        ];
        if ($data['developer_id']) $data['status'] = 'in_progress';

        $errors = $this->validate($data, ['title' => 'required|max:255', 'deal_id' => 'required']);
        if ($errors) {
            Session::flash('errors', $errors);
            $this->redirect(APP_URL . '/admin/projects/create/' . $data['deal_id']);
            return;
        }

        $model     = new Project();
        $projectId = $model->create($data);

        // Create default phases if requested
        $defaultPhases = $_POST['default_phases'] ?? [];
        $phaseNames    = [
            'requirements' => 'Requirements & Planning',
            'design'       => 'UI/UX Design',
            'development'  => 'Development',
            'testing'      => 'Testing & QA',
            'deployment'   => 'Deployment',
            'handover'     => 'Client Handover',
        ];
        $order = 0;
        foreach ($defaultPhases as $key) {
            if (isset($phaseNames[$key])) {
                $model->addPhase([
                    'project_id' => $projectId,
                    'name'       => $phaseNames[$key],
                    'order_num'  => $order++,
                ]);
            }
        }

        Session::flash('success', 'Project created successfully.');
        $this->redirect(APP_URL . '/admin/projects/' . $projectId);
    }

    // ── Admin: edit project ───────────────────────────────────────────────────
    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $model   = new Project();
        $project = $model->find((int)$id);
        if (!$project) { $this->redirect(APP_URL . '/admin/projects'); return; }

        $developers = (new User())->developers();
        $unread     = (new Message())->unreadCount(Auth::id());

        $this->view('admin.projects.edit', [
            'title'      => 'Edit Project',
            'project'    => $project,
            'developers' => $developers,
            'unread'     => $unread,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data = [
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'awaiting_assignment',
            'priority'    => $_POST['priority'] ?? 'medium',
            'start_date'  => $_POST['start_date'] ?: null,
            'deadline'    => $_POST['deadline'] ?: null,
            'budget'      => (float)($_POST['budget'] ?? 0),
            'tech_stack'  => trim($_POST['tech_stack'] ?? ''),
            'repo_url'    => trim($_POST['repo_url'] ?? ''),
            'staging_url' => trim($_POST['staging_url'] ?? ''),
            'live_url'    => trim($_POST['live_url'] ?? ''),
        ];
        if ($data['status'] === 'completed') {
            $data['actual_end'] = date('Y-m-d');
        }

        (new Project())->update((int)$id, $data);
        Session::flash('success', 'Project updated.');
        $this->redirect(APP_URL . '/admin/projects/' . $id);
    }

    // ── Admin: assign developer ───────────────────────────────────────────────
    public function assignDeveloper(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $developerId = (int)($_POST['developer_id'] ?? 0);
        $model       = new Project();
        $project     = $model->find((int)$id);
        if (!$project) { $this->redirect(APP_URL . '/admin/projects'); return; }

        $model->update((int)$id, [
            'developer_id' => $developerId ?: null,
            'status'       => $developerId ? 'in_progress' : 'awaiting_assignment',
        ]);

        // Also update the deal
        (new Deal())->update($project['deal_id'], ['developer_id' => $developerId ?: null]);

        Session::flash('success', 'Developer assigned.');
        $this->redirect(APP_URL . '/admin/projects/' . $id);
    }

    // ── Phases ────────────────────────────────────────────────────────────────
    public function addPhase(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $projectId = (int)($_POST['project_id'] ?? 0);
        $data      = [
            'project_id'  => $projectId,
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'order_num'   => (int)($_POST['order_num'] ?? 0),
            'due_date'    => $_POST['due_date'] ?: null,
            'status'      => 'pending',
        ];

        if ($data['name']) {
            (new Project())->addPhase($data);
            Session::flash('success', 'Phase added.');
        }
        $this->redirect(APP_URL . '/admin/projects/' . $projectId);
    }

    public function updatePhase(string $phaseId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model  = new Project();
        $phase  = $model->getPhase((int)$phaseId);
        if (!$phase) { $this->back(); return; }

        $data = [
            'name'        => trim($_POST['name'] ?? $phase['name']),
            'description' => trim($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? $phase['status'],
            'due_date'    => $_POST['due_date'] ?: null,
            'order_num'   => (int)($_POST['order_num'] ?? $phase['order_num']),
        ];

        $model->updatePhase((int)$phaseId, $data);
        Session::flash('success', 'Phase updated.');
        $this->redirect(APP_URL . '/admin/projects/' . $phase['project_id']);
    }

    public function deletePhase(string $phaseId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $model = new Project();
        $phase = $model->getPhase((int)$phaseId);
        if ($phase) {
            $model->deletePhase((int)$phaseId);
            Session::flash('success', 'Phase deleted.');
            $this->redirect(APP_URL . '/admin/projects/' . $phase['project_id']);
        } else {
            $this->back();
        }
    }

    // ── Notes ─────────────────────────────────────────────────────────────────
    public function addNote(): void
    {
        CSRF::check();
        Auth::requireLogin();

        $projectId = (int)($_POST['project_id'] ?? 0);
        $body      = trim($_POST['body'] ?? '');

        if ($body && $projectId) {
            (new Project())->addNote([
                'project_id'  => $projectId,
                'user_id'     => Auth::id(),
                'body'        => $body,
                'is_internal' => (int)($_POST['is_internal'] ?? 0),
            ]);
            Session::flash('success', 'Note added.');
        }

        if (Auth::isAdmin()) {
            $this->redirect(APP_URL . '/admin/projects/' . $projectId);
        } else {
            $this->redirect(APP_URL . '/developer/projects/' . $projectId);
        }
    }

    // ── Developer: list own projects ──────────────────────────────────────────
    public function developerIndex(): void
    {
        Auth::requireDeveloper();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'   => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
        ];
        $model  = new Project();
        $result = $model->forDeveloper(Auth::id(), $filters, $page, 20);
        $stats  = $model->developerStats(Auth::id());
        $unread = (new Message())->unreadCount(Auth::id());

        $this->view('developer.projects.index', $result + [
            'title'   => 'My Projects',
            'filters' => $filters,
            'stats'   => $stats,
            'unread'  => $unread,
        ]);
    }

    // ── Developer: show project detail ────────────────────────────────────────
    public function developerShow(string $id): void
    {
        Auth::requireDeveloper();
        $model   = new Project();
        $project = $model->findWithDetails((int)$id);

        if (!$project || ($project['developer_id'] != Auth::id() && !Auth::isAdmin())) {
            $this->redirect(APP_URL . '/developer/projects');
            return;
        }

        $phases    = $model->getPhases((int)$id);
        $notes     = $model->getNotes((int)$id, Auth::isAdmin());
        $contracts = (new \App\Models\Contract())->forDeal($project['deal_id']);
        $invoices  = (new \App\Models\Invoice())->forDeal($project['deal_id']);
        $unread    = (new Message())->unreadCount(Auth::id());

        $this->view('developer.projects.show', [
            'title'     => $project['title'],
            'project'   => $project,
            'phases'    => $phases,
            'notes'     => $notes,
            'contracts' => $contracts,
            'invoices'  => $invoices,
            'unread'    => $unread,
        ]);
    }

    // ── Developer: update project status ─────────────────────────────────────
    public function updateStatus(string $id): void
    {
        Auth::requireDeveloper();
        CSRF::check();

        $model   = new Project();
        $project = $model->find((int)$id);
        if (!$project || ($project['developer_id'] != Auth::id() && !Auth::isAdmin())) {
            $this->back(); return;
        }

        $allowed = ['in_progress', 'testing', 'on_hold', 'completed'];
        $status  = $_POST['status'] ?? '';
        if (in_array($status, $allowed)) {
            $upd = ['status' => $status];
            if ($status === 'completed') $upd['actual_end'] = date('Y-m-d');
            // Also update URLs if provided
            foreach (['repo_url', 'staging_url', 'live_url', 'tech_stack'] as $field) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $upd[$field] = trim($_POST[$field]);
                }
            }
            $model->update((int)$id, $upd);
            Session::flash('success', 'Project status updated.');
        }
        $this->redirect(APP_URL . '/developer/projects/' . $id);
    }

    // ── Developer: update phase status ────────────────────────────────────────
    public function updatePhaseStatus(string $phaseId): void
    {
        Auth::requireDeveloper();
        CSRF::check();

        $model  = new Project();
        $phase  = $model->getPhase((int)$phaseId);
        if (!$phase) { $this->back(); return; }

        $project = $model->find($phase['project_id']);
        if (!$project || ($project['developer_id'] != Auth::id() && !Auth::isAdmin())) {
            $this->back(); return;
        }

        $allowed = ['pending', 'in_progress', 'completed', 'skipped'];
        $status  = $_POST['status'] ?? '';
        if (in_array($status, $allowed)) {
            $model->updatePhase((int)$phaseId, ['status' => $status]);
            Session::flash('success', 'Phase updated.');
        }
        $this->redirect(APP_URL . '/developer/projects/' . $phase['project_id']);
    }

    // ── Admin: assign user to project team ────────────────────────────────────
    public function assignUser(string $projectId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $userId   = (int)($_POST['user_id']   ?? 0);
        $roleType = $_POST['role_type'] ?? 'developer';
        $notes    = trim($_POST['notes'] ?? '');
        $valid    = ['caller', 'developer', 'partner'];

        if ($userId && in_array($roleType, $valid)) {
            (new Project())->addAssignment((int)$projectId, $userId, $roleType, Auth::id(), $notes);
            Session::flash('success', 'Team member assigned.');
        }
        $this->redirect(APP_URL . '/admin/projects/' . $projectId);
    }

    // ── Admin: remove user from project team ─────────────────────────────────
    public function removeUser(string $assignmentId): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $projectId = (int)($_POST['project_id'] ?? 0);
        (new Project())->removeAssignment((int)$assignmentId);
        Session::flash('success', 'Team member removed.');
        $this->redirect(APP_URL . '/admin/projects/' . $projectId);
    }
}
