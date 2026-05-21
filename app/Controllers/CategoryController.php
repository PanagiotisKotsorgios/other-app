<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\{Category, Message};

class CategoryController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $categories = (new Category())->all();
        $unread     = (new Message())->unreadCount(Auth::id());

        $this->view('admin.categories.index', [
            'title'      => 'Productivity Categories',
            'categories' => $categories,
            'unread'     => $unread,
        ]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.categories.create', [
            'title'  => 'Create Category',
            'old'    => Session::getFlash('old', []),
            'errors' => Session::getFlash('errors', []),
            'unread' => $unread,
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $data   = $this->collectPost();
        $errors = $this->validate($data, [
            'name'           => 'required|max:20',
            'caller_rate'    => 'required|numeric',
            'developer_rate' => 'required|numeric',
            'partner_rate'   => 'required|numeric',
        ]);

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect(APP_URL . '/admin/categories/create');
            return;
        }

        (new Category())->create($data);
        Session::flash('success', 'Category "' . htmlspecialchars($data['name']) . '" created.');
        $this->redirect(APP_URL . '/admin/categories');
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $category = (new Category())->find((int)$id);
        if (!$category) { $this->redirect(APP_URL . '/admin/categories'); return; }

        $unread = (new Message())->unreadCount(Auth::id());
        $this->view('admin.categories.edit', [
            'title'    => 'Edit Category',
            'category' => $category,
            'errors'   => Session::getFlash('errors', []),
            'unread'   => $unread,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $category = (new Category())->find((int)$id);
        if (!$category) { $this->redirect(APP_URL . '/admin/categories'); return; }

        $data   = $this->collectPost();
        $errors = $this->validate($data, [
            'name'           => 'required|max:20',
            'caller_rate'    => 'required|numeric',
            'developer_rate' => 'required|numeric',
            'partner_rate'   => 'required|numeric',
        ]);

        if ($errors) {
            Session::flash('errors', $errors);
            $this->redirect(APP_URL . '/admin/categories/' . $id . '/edit');
            return;
        }

        (new Category())->update((int)$id, $data);
        Session::flash('success', 'Category updated.');
        $this->redirect(APP_URL . '/admin/categories');
    }

    public function delete(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        // Set category_id = NULL on users before deleting (FK is ON DELETE SET NULL so DB handles it)
        (new Category())->delete((int)$id);
        Session::flash('success', 'Category deleted.');
        $this->redirect(APP_URL . '/admin/categories');
    }

    private function collectPost(): array
    {
        return [
            'name'           => strtoupper(trim($_POST['name'] ?? '')),
            'label'          => trim($_POST['label'] ?? ''),
            'caller_rate'    => (float)($_POST['caller_rate'] ?? 0),
            'developer_rate' => (float)($_POST['developer_rate'] ?? 0),
            'partner_rate'   => (float)($_POST['partner_rate'] ?? 0),
            'color'          => trim($_POST['color'] ?? 'blue'),
            'description'    => trim($_POST['description'] ?? ''),
            'sort_order'     => (int)($_POST['sort_order'] ?? 0),
        ];
    }
}
