<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/CSRF.php';
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">My Dashboard</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

            <!-- Next Appointment Card -->
            <?php if ($nextAppt): ?>
            <div class="row">
                <div class="col-12">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-calendar-check mr-2"></i>Next Appointment</h5>
                        <p>
                            <strong>Dr. <?= e($nextAppt['doctor_name']) ?></strong> — <?= e($nextAppt['specialization_name']) ?><br>
                            <i class="fas fa-calendar mr-1"></i> <?= formatDate($nextAppt['appt_date']) ?>
                            &nbsp; <i class="fas fa-clock mr-1"></i> <?= formatTime($nextAppt['appt_time']) ?>
                            &nbsp; <?= statusBadge($nextAppt['status']) ?>
                        </p>
                        <a href="<?= url('appointments','view',['id'=>$nextAppt['id']]) ?>" class="btn btn-sm btn-info">View Details</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= (int)($stats['active'] ?? 0) ?></h3>
                            <p>Active Appointments</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                        <a href="<?= url('appointments') ?>" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= (int)($stats['completed'] ?? 0) ?></h3>
                            <p>Completed</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $prescCount ?></h3>
                            <p>Prescriptions</p>
                        </div>
                        <div class="icon"><i class="fas fa-file-medical"></i></div>
                        <a href="<?= url('prescriptions') ?>" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <a href="<?= url('appointments','book') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle mr-2"></i>Book New Appointment
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
