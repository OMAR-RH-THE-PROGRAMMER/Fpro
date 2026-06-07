<?php
$role    = Auth::role();
$curPage = $_GET['page'] ?? 'dashboard';
$curAct  = $_GET['action'] ?? '';

function isActive(string $page, string $action = ''): string {
    global $curPage, $curAct;
    if ($action) {
        return ($curPage === $page && $curAct === $action) ? 'active' : '';
    }
    return $curPage === $page ? 'active' : '';
}

$user = Auth::currentUser();
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= url('dashboard') ?>" class="brand-link">
        <i class="fas fa-clinic-medical brand-image ml-3"></i>
        <span class="brand-text font-weight-bold"><?= APP_NAME ?></span>
    </a>
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-white ml-1"></i>
            </div>
            <div class="info">
                <a href="<?= url('users','profile') ?>" class="d-block text-truncate" style="max-width:140px">
                    <?= e($user['name'] ?? '') ?>
                </a>
                <small class="badge badge-secondary"><?= e($role) ?></small>
            </div>
        </div>
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                <li class="nav-item">
                    <a href="<?= url('dashboard') ?>" class="nav-link <?= isActive('dashboard') ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php if ($role === 'admin'): ?>
                <li class="nav-header">ADMINISTRATION</li>
                <li class="nav-item">
                    <a href="<?= url('users') ?>" class="nav-link <?= isActive('users') ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('doctors') ?>" class="nav-link <?= isActive('doctors') ?>">
                        <i class="nav-icon fas fa-user-md"></i>
                        <p>Doctors</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('doctors','specializations') ?>" class="nav-link <?= isActive('doctors','specializations') ?>">
                        <i class="nav-icon fas fa-stethoscope"></i>
                        <p>Specializations</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('appointments') ?>" class="nav-link <?= isActive('appointments') ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Appointments</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('reports') ?>" class="nav-link <?= isActive('reports') ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Reports</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($role === 'doctor'): ?>
                <li class="nav-header">DOCTOR</li>
                <li class="nav-item">
                    <a href="<?= url('appointments') ?>" class="nav-link <?= isActive('appointments') ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>My Schedule</p>
                    </a>
                </li>
                <?php
                    require_once __DIR__ . '/../../models/DoctorModel.php';
                    $myDoc = (new DoctorModel())->findByUserId($user['id']);
                    if ($myDoc):
                ?>
                <li class="nav-item">
                    <a href="<?= url('doctors','edit',['id'=>$myDoc['id']]) ?>" class="nav-link">
                        <i class="nav-icon fas fa-id-badge"></i>
                        <p>My Profile</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <?php if ($role === 'patient'): ?>
                <li class="nav-header">PATIENT</li>
                <li class="nav-item">
                    <a href="<?= url('appointments','book') ?>" class="nav-link <?= isActive('appointments','book') ?>">
                        <i class="nav-icon fas fa-plus-circle"></i>
                        <p>Book Appointment</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('appointments') ?>" class="nav-link <?= isActive('appointments') ?>">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>My Appointments</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= url('prescriptions') ?>" class="nav-link <?= isActive('prescriptions') ?>">
                        <i class="nav-icon fas fa-file-medical"></i>
                        <p>My Prescriptions</p>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-header">ACCOUNT</li>
                <li class="nav-item">
                    <a href="<?= url('users','profile') ?>" class="nav-link <?= isActive('users','profile') ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>My Profile</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
