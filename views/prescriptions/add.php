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
            <h1>Add Prescription</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>
            <div class="callout callout-info">
                <strong>Appointment #<?= (int)$appt['id'] ?></strong> —
                Patient: <?= e($appt['patient_name']) ?> |
                Date: <?= formatDate($appt['appt_date']) ?> <?= formatTime($appt['appt_time']) ?>
            </div>
            <div class="card card-success">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-medical-alt mr-2"></i>New Prescription</h3></div>
                <form method="POST" action="<?= url('prescriptions','store') ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <input type="hidden" name="appointment_id" value="<?= (int)$appt['id'] ?>">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Diagnosis *</label>
                            <textarea name="diagnosis" class="form-control" rows="3" required placeholder="Describe the diagnosis..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Medications *</label>
                            <textarea name="medications" class="form-control" rows="4" required placeholder="List medications, dosage, and instructions..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Follow-up instructions, rest recommendations..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Prescription PDF <small class="text-muted">(optional, max 3MB)</small></label>
                            <input type="file" name="prescription_file" class="form-control-file" accept=".pdf">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Save Prescription
                        </button>
                        <a href="<?= url('appointments','view',['id'=>$appt['id']]) ?>" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
