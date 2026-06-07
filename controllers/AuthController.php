<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {

    public function login(): void {
        if (Auth::check()) {
            redirect(url('dashboard'));
        }
        $pageTitle = 'Login — ' . APP_NAME;
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function handleLogin(): void {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid security token. Please try again.');
            redirect(url('auth', 'login'));
        }

        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        $model = new UserModel();
        $user  = $model->findByEmail($email);

        if (!$user) {
            setFlash('danger', 'Invalid credentials.');
            redirect(url('auth', 'login'));
        }

        if ((int) $user['is_active'] !== 1) {
            setFlash('warning', 'Account suspended. Contact admin.');
            redirect(url('auth', 'login'));
        }

        if (!password_verify($password, $user['password'])) {
            setFlash('danger', 'Invalid credentials.');
            redirect(url('auth', 'login'));
        }

        Auth::login($user);
        redirect(url('dashboard'));
    }

    public function handleLogout(): void {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            redirect(url('dashboard'));
        }
        Auth::logout();
    }
}
