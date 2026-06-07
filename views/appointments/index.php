<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/CSRF.php';
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
$role = Auth::role();
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Appointments</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Appointments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

            <!-- Filter Form -->
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filter</h3>
                    <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= url('appointments') ?>" class="form-inline flex-wrap">
                        <input type="hidden" name="page" value="appointments">
                        <div class="form-group mr-2 mb-2">
                            <label class="mr-1">Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <?php foreach (['pending','confirmed','completed','cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= ($_GET['status']??'')===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($role === 'admin'): ?>
                        <div class="form-group mr-2 mb-2">
                            <label class="mr-1">Doctor</label>
                            <select name="doctor_id" class="form-control form-control-sm">
                                <option value="">All Doctors</option>
                                <?php foreach ($doctors as $d): ?>
                                <option value="<?= (int)$d['id'] ?>" <?= ($_GET['doctor_id']??'')==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <input type="text" name="patient_name" class="form-control form-control-sm" placeholder="Patient name" value="<?= e($_GET['patient_name']??'') ?>">
                        </div>
                        <?php endif; ?>
                        <div class="form-group mr-2 mb-2">
                            <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($_GET['start_date']??'') ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($_GET['end_date']??'') ?>">
                        </div>
                        <button class="btn btn-sm btn-secondary mb-2 mr-1"><i class="fas fa-search"></i> Search</button>
                        <a href="<?= url('appointments') ?>" class="btn btn-sm btn-link mb-2">Reset</a>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Results (<?= $total ?>)</h3>
                    <?php if ($role === 'patient'): ?>
                    <div class="card-tools">
                        <a href="<?= url('appointments','book') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Book Appointment
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <?php if ($role !== 'patient'): ?><th>Patient</th><?php endif; ?>
                                <?php if ($role !== 'doctor'): ?><th>Doctor</th><?php endif; ?>
                                <th>Date</th><th>Time</th><th>Status</th><th>Reason</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($appointments as $a): ?>
                        <tr>
                            <?php if ($role !== 'patient'): ?>
                            <td><?= e($a['patient_name']) ?></td>
                            <?php endif; ?>
                            <?php if ($role !== 'doctor'): ?>
                            <td>
                                <?= e($a['doctor_name'] ?? '') ?>
                                <?php if (!empty($a['specialization_name'])): ?>
                                <br><small class="text-muted"><?= e($a['specialization_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <td><?= formatDate($a['appt_date']) ?></td>
                            <td><?= formatTime($a['appt_time']) ?></td>
                            <td><?= statusBadge($a['status']) ?></td>
                            <td><small><?= e($a['reason'] ?? '—') ?></small></td>
                            <td>
                                <a href="<?= url('appointments','view',['id'=>$a['id']]) ?>" class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($role === 'patient' && $a['status'] === 'pending'): ?>
                                <form method="POST" action="<?= url('appointments','cancel') ?>" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Cancel this appointment?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if ($role === 'patient' && $a['status'] === 'completed' && !empty($a['prescription_id'])): ?>
                                <a href="<?= url('appointments','view',['id'=>$a['id']]) ?>" class="btn btn-xs btn-success">
                                    <i class="fas fa-file-medical"></i> Rx
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($appointments)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No appointments found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php if ($pager->totalPages() > 1): ?>
                <div class="card-footer">
                    <nav><ul class="pagination pagination-sm mb-0">
                        <?php if ($pager->hasPrev()): ?>
                        <li class="page-item"><a class="page-link" href="<?= url('appointments','',array_merge($_GET,['p'=>$pager->getCurrentPage()-1])) ?>">«</a></li>
                        <?php endif; ?>
                        <?php for ($i=1;$i<=$pager->totalPages();$i++): ?>
                        <li class="page-item <?= $i==$pager->getCurrentPage()?'active':'' ?>">
                            <a class="page-link" href="<?= url('appointments','',array_merge($_GET,['p'=>$i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <?php if ($pager->hasNext()): ?>
                        <li class="page-item"><a class="page-link" href="<?= url('appointments','',array_merge($_GET,['p'=>$pager->getCurrentPage()+1])) ?>">»</a></li>
                        <?php endif; ?>
                    </ul></nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
