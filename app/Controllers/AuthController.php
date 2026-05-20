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
        $this->view('auth.profile', ['title' => 'My Profile', 'user' => $user]);
    }

    public function profileUpdate(): void
    {
        Auth::requireLogin();
        CSRF::check();

        $data = [
            'name'  => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
        ];

        $userModel = new User();
        $userModel->update(Auth::id(), $data);
        Session::set('user_name', $data['name']);
        Session::flash('success', 'Profile updated.');
        $this->redirect(APP_URL . '/auth/profile');
    }

    public function passwordUpdate(): void
    {
        Auth::requireLogin();
        CSRF::check();

        $current  = $_POST['current_password'] ?? '';
        $new      = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $userModel = new User();
        $user      = $userModel->find(Auth::id());

        if (!password_verify($current, $user['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            $this->redirect(APP_URL . '/auth/profile');
            return;
        }

        if ($new !== $confirm || strlen($new) < 8) {
            Session::flash('error', 'New passwords do not match or are too short (min 8 chars).');
            $this->redirect(APP_URL . '/auth/profile');
            return;
        }

        $userModel->updatePassword(Auth::id(), $new);
        Session::flash('success', 'Password changed successfully.');
        $this->redirect(APP_URL . '/auth/profile');
    }
}
