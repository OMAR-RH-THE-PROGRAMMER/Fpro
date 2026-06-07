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
                <div class="col-sm-6"><h1>Appointment #<?= (int)$appt['id'] ?></h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('appointments') ?>">Appointments</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>
            <div class="row">
                <!-- Appointment Info -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Details</h3></div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Patient</dt>
                                <dd class="col-sm-8"><?= e($appt['patient_name']) ?> <small class="text-muted"><?= e($appt['patient_phone']??'') ?></small></dd>
                                <dt class="col-sm-4">Doctor</dt>
                                <dd class="col-sm-8">Dr. <?= e($appt['doctor_name']) ?></dd>
                                <dt class="col-sm-4">Specialization</dt>
                                <dd class="col-sm-8"><?= e($appt['specialization_name']) ?></dd>
                                <dt class="col-sm-4">Date</dt>
                                <dd class="col-sm-8"><?= formatDate($appt['appt_date']) ?></dd>
                                <dt class="col-sm-4">Time</dt>
                                <dd class="col-sm-8"><?= formatTime($appt['appt_time']) ?></dd>
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8"><?= statusBadge($appt['status']) ?></dd>
                                <dt class="col-sm-4">Reason</dt>
                                <dd class="col-sm-8"><?= e($appt['reason'] ?? '—') ?></dd>
                                <?php if ($appt['doctor_notes']): ?>
                                <dt class="col-sm-4">Doctor Notes</dt>
                                <dd class="col-sm-8"><?= nl2br(e($appt['doctor_notes'])) ?></dd>
                                <?php endif; ?>
                                <dt class="col-sm-4">Fee</dt>
                                <dd class="col-sm-8">$<?= number_format($appt['consultation_fee'],2) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-md-6">
                    <?php if ($role === 'doctor' || $role === 'admin'): ?>
                    <div class="card card-warning">
                        <div class="card-header"><h3 class="card-title">Update Status</h3></div>
                        <form method="POST" action="<?= url('appointments','update_status') ?>">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                            <input type="hidden" name="id" value="<?= (int)$appt['id'] ?>">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>New Status</label>
                                    <select name="status" class="form-control">
                                        <?php foreach (['pending','confirmed','completed','cancelled'] as $st): ?>
                                        <option value="<?= $st ?>" <?= $appt['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Doctor Notes</label>
                                    <textarea name="doctor_notes" class="form-control" rows="3"><?= e($appt['doctor_notes']??'') ?></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning btn-block">
                                    <i class="fas fa-save mr-1"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Prescription -->
                    <?php if (!empty($appt['prescription_id'])): ?>
                    <div class="card card-success">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-file-medical mr-2"></i>Prescription</h3></div>
                        <div class="card-body">
                            <p><b>Diagnosis:</b> <?= nl2br(e($appt['diagnosis']??'')) ?></p>
                            <p><b>Medications:</b> <?= nl2br(e($appt['medications']??'')) ?></p>
                            <?php if ($appt['presc_notes']): ?>
                            <p><b>Notes:</b> <?= nl2br(e($appt['presc_notes'])) ?></p>
                            <?php endif; ?>
                            <?php if ($appt['presc_file']): ?>
                            <a href="<?= url('prescriptions','download',['id'=>$appt['prescription_id']]) ?>"
                               class="btn btn-success btn-sm">
                                <i class="fas fa-download mr-1"></i> Download PDF
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php elseif ($role === 'doctor' && $appt['status'] === 'completed'): ?>
                    <div class="callout callout-info">
                        <p>No prescription yet.</p>
                        <a href="<?= url('prescriptions','add',['appt_id'=>$appt['id']]) ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Add Prescription
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($role === 'patient' && $appt['status'] === 'pending'): ?>
                    <div class="card card-danger">
                        <div class="card-body">
                            <form method="POST" action="<?= url('appointments','cancel') ?>">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                <input type="hidden" name="id" value="<?= (int)$appt['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-block"
                                        onclick="return confirm('Cancel this appointment?')">
                                    <i class="fas fa-times mr-1"></i> Cancel Appointment
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <a href="<?= url('appointments') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
