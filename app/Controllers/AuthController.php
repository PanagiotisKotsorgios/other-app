<?php

namespace App\Controllers;

use App\Core\{Controller, Auth, CSRF, Session};
use App\Models\User;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect(APP_URL . (Auth::isAdmin() ? '/admin/dashboard' : '/caller/dashboard'));
        }
        $this->view('auth.login', ['title' => 'Login'], 'auth');
    }

    public function login(): void
    {
        CSRF::check();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = $this->validate(compact('email', 'password'), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', compact('email'));
            $this->redirect(APP_URL . '/auth/login');
            return;
        }

        $userModel = new User();
        $user      = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password']) || !$user['is_active']) {
            Session::flash('error', 'Invalid credentials or account disabled.');
            Session::flash('old', compact('email'));
            $this->redirect(APP_URL . '/auth/login');
            return;
        }

        Auth::login($user);
        $this->redirect(Auth::dashboardUrl());
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect(APP_URL . '/auth/login');
    }

    public function profileForm(): void
    {
        Auth::requireLogin();
        $userModel = new User();
        $user      = $userModel->find(Auth::id());
        $this->view('auth.profile', ['title' => 'Το Προφίλ μου', 'user' => $user]);
    }

    public function profileUpdate(): void
    {
        Auth::requireLogin();
        CSRF::check();

        $userModel = new User();
        $email     = trim($_POST['email'] ?? '');

        if ($email) {
            $existing = $userModel->findByEmail($email);
            if ($existing && (int)$existing['id'] !== Auth::id()) {
                Session::flash('error', 'Το email χρησιμοποιείται ήδη από άλλο λογαριασμό.');
                $this->redirect(APP_URL . '/auth/profile');
                return;
            }
        }

        $data = [
            'name'  => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ];
        if ($email) {
            $data['email'] = $email;
        }

        $userModel->update(Auth::id(), $data);
        Session::set('user_name', $data['name']);
        Session::flash('success', 'Το προφίλ σας ενημερώθηκε.');
        $this->redirect(APP_URL . '/auth/profile');
    }

    public function passwordUpdate(): void
    {
        Auth::requireLogin();
        CSRF::check();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $userModel = new User();
        $user      = $userModel->find(Auth::id());

        if (!password_verify($current, $user['password'])) {
            Session::flash('error', 'Ο τρέχων κωδικός είναι λανθασμένος.');
            $this->redirect(APP_URL . '/auth/profile');
            return;
        }

        if (strlen($new) < 8) {
            Session::flash('error', 'Ο νέος κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.');
            $this->redirect(APP_URL . '/auth/profile');
            return;
        }

        if ($new !== $confirm) {
            Session::flash('error', 'Οι κωδικοί δεν ταιριάζουν.');
            $this->redirect(APP_URL . '/auth/profile');
            return;
        }

        $userModel->updatePassword(Auth::id(), $new);
        Session::flash('success', 'Ο κωδικός σας άλλαξε επιτυχώς.');
        $this->redirect(APP_URL . '/auth/profile');
    }
}
