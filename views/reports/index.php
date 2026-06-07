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
                <div class="col-sm-6"><h1>Reports</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>

            <!-- Filter Form -->
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-2"></i>Report Filters</h3></div>
                <form method="GET" action="<?= url('reports') ?>">
                    <input type="hidden" name="page" value="reports">
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Start Date *</label>
                                    <input type="date" name="start_date" class="form-control" required
                                           value="<?= e($filters['start_date']) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Date *</label>
                                    <input type="date" name="end_date" class="form-control" required
                                           value="<?= e($filters['end_date']) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Doctor</label>
                                    <select name="doctor_id" class="form-control">
                                        <option value="">All Doctors</option>
                                        <?php foreach ($doctors as $d): ?>
                                        <option value="<?= (int)$d['id'] ?>" <?= $filters['doctor_id']==$d['id']?'selected':'' ?>>
                                            Dr. <?= e($d['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <?php foreach (['pending','confirmed','completed','cancelled'] as $st): ?>
                                        <option value="<?= $st ?>" <?= $filters['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary mr-2">
                            <i class="fas fa-search mr-1"></i> Generate Report
                        </button>
                        <?php if (!empty($appointments)): ?>
                        <a href="<?= url('reports','','',array_merge($filters,['export'=>'csv'])) ?>"
                           class="btn btn-success">
                            <i class="fas fa-file-csv mr-1"></i> Export CSV
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <?php if (!empty($appointments)): ?>
            <?php
                $statusGroups = [];
                foreach ($appointments as $a) {
                    $statusGroups[$a['status']] = ($statusGroups[$a['status']] ?? 0) + 1;
                }
            ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Results — <?= count($appointments) ?> appointment(s)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped data-table">
                        <thead>
                            <tr>
                                <th>Patient</th><th>Doctor</th><th>Specialization</th>
                                <th>Date</th><th>Time</th><th>Status</th><th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($appointments as $a): ?>
                        <tr>
                            <td><?= e($a['patient_name']) ?></td>
                            <td>Dr. <?= e($a['doctor_name']) ?></td>
                            <td><?= e($a['specialization_name']) ?></td>
                            <td><?= formatDate($a['appt_date']) ?></td>
                            <td><?= formatTime($a['appt_time']) ?></td>
                            <td><?= statusBadge($a['status']) ?></td>
                            <td><?= e($a['reason'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="5"><strong>Total: <?= count($appointments) ?></strong></td>
                                <td colspan="2">
                                    <?php foreach ($statusGroups as $st => $cnt): ?>
                                    <?= statusBadge($st) ?> <?= $cnt ?> &nbsp;
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php elseif (!empty($filters['start_date']) && empty($error)): ?>
            <div class="alert alert-info">No appointments found for the selected criteria.</div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
