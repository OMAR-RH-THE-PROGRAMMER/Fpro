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
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Admin Dashboard</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

            <!-- Stats Row -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= (int)($usersByRole['doctor'] ?? 0) ?></h3>
                            <p>Doctors</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-md"></i></div>
                        <a href="<?= url('doctors') ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= (int)($usersByRole['patient'] ?? 0) ?></h3>
                            <p>Patients</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <a href="<?= url('users','','',['role'=>'patient']) ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $todayCount ?></h3>
                            <p>Today's Appointments</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-day"></i></div>
                        <a href="<?= url('appointments') ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= (int)($weekByStatus['pending'] ?? 0) ?></h3>
                            <p>Pending This Week</p>
                        </div>
                        <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                        <a href="<?= url('appointments') ?>" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Week status row -->
            <div class="row">
                <?php
                $statusColors = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
                foreach ($statusColors as $st => $color):
                    $cnt = (int)($weekByStatus[$st] ?? 0);
                ?>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-<?= $color ?>">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text"><?= ucfirst($st) ?> (week)</span>
                            <span class="info-box-number"><?= $cnt ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Chart -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Appointments – Last 14 Days</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="apptChart" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Recent Appointments</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentAppts as $a): ?>
                                <tr>
                                    <td><?= e($a['patient_name']) ?></td>
                                    <td><?= e($a['doctor_name']) ?></td>
                                    <td><?= formatDate($a['appt_date']) ?></td>
                                    <td><?= statusBadge($a['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
<?php
$labels = json_encode(array_column($chartData, 'appt_date'));
$values = json_encode(array_column($chartData, 'total'));
$extraJs = <<<JS
<script>
var ctx = document.getElementById('apptChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: $labels,
        datasets: [{
            label: 'Appointments',
            data: $values,
            backgroundColor: 'rgba(60,141,188,0.8)'
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});
</script>
JS;
require_once __DIR__ . '/../partials/footer.php';
?>
