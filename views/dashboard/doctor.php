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
            <h1 class="m-0">Doctor Dashboard</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

            <!-- Stats -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= count($todayAppts) ?></h3>
                            <p>Today's Appointments</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-day"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $monthCount ?></h3>
                            <p>This Month</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= count($upcoming) ?></h3>
                            <p>Upcoming</p>
                        </div>
                        <div class="icon"><i class="fas fa-hourglass-start"></i></div>
                    </div>
                </div>
            </div>

            <!-- Doctor Info Card -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <i class="fas fa-user-md fa-4x text-primary"></i>
                            </div>
                            <h3 class="profile-username text-center"><?= e($doctor['name']) ?></h3>
                            <p class="text-muted text-center"><?= e($doctor['specialization_name']) ?></p>
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Fee</b> <span class="float-right">$<?= number_format($doctor['consultation_fee'],2) ?></span>
                                </li>
                                <li class="list-group-item">
                                    <b>Available Days</b>
                                    <span class="float-right"><?= e($doctor['available_days']) ?></span>
                                </li>
                            </ul>
                            <a href="<?= url('doctors','edit',['id'=>$doctor['id']]) ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-edit mr-1"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <!-- Today's Schedule -->
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title text-white"><i class="fas fa-calendar-day mr-2"></i>Today's Schedule</h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($todayAppts)): ?>
                                <p class="p-3 text-muted">No appointments today.</p>
                            <?php else: ?>
                            <table class="table table-sm">
                                <thead><tr><th>Time</th><th>Patient</th><th>Reason</th><th>Status</th><th></th></tr></thead>
                                <tbody>
                                <?php foreach ($todayAppts as $a): ?>
                                <tr>
                                    <td><?= formatTime($a['appt_time']) ?></td>
                                    <td><?= e($a['patient_name']) ?></td>
                                    <td><?= e($a['reason'] ?? '—') ?></td>
                                    <td><?= statusBadge($a['status']) ?></td>
                                    <td><a href="<?= url('appointments','view',['id'=>$a['id']]) ?>" class="btn btn-xs btn-info">View</a></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upcoming -->
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Upcoming Appointments</h3></div>
                        <div class="card-body p-0">
                            <?php if (empty($upcoming)): ?>
                                <p class="p-3 text-muted">No upcoming appointments.</p>
                            <?php else: ?>
                            <table class="table table-sm">
                                <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($upcoming as $a): ?>
                                <tr>
                                    <td><?= formatDate($a['appt_date']) ?></td>
                                    <td><?= formatTime($a['appt_time']) ?></td>
                                    <td><?= e($a['patient_name']) ?></td>
                                    <td><?= statusBadge($a['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
