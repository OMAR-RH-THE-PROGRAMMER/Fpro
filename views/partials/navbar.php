<?php
$currentUser = Auth::currentUser();
$role        = Auth::role();
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= url('dashboard') ?>" class="nav-link"><?= APP_NAME ?></a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-user-circle mr-1"></i>
                <?= e($currentUser['name'] ?? '') ?>
                <span class="badge badge-secondary ml-1"><?= e($role) ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="<?= url('users', 'profile') ?>" class="dropdown-item">
                    <i class="fas fa-id-card mr-2"></i> My Profile
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="<?= url('auth', 'logout') ?>">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
