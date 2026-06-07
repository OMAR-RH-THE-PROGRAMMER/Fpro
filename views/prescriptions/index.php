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
            <h1>My Prescriptions</h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-medical mr-2"></i>Prescription History</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped data-table">
                        <thead>
                            <tr><th>Date</th><th>Doctor</th><th>Specialization</th><th>Diagnosis</th><th>Download</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($prescriptions as $p): ?>
                        <tr>
                            <td><?= formatDate($p['appt_date']) ?></td>
                            <td>Dr. <?= e($p['doctor_name']) ?></td>
                            <td><?= e($p['specialization_name']) ?></td>
                            <td><?= e(mb_substr($p['diagnosis'], 0, 60)) ?><?= strlen($p['diagnosis'])>60?'…':'' ?></td>
                            <td>
                                <?php if ($p['file_path']): ?>
                                <a href="<?= url('prescriptions','download',['id'=>$p['id']]) ?>"
                                   class="btn btn-xs btn-success">
                                    <i class="fas fa-download mr-1"></i> PDF
                                </a>
                                <?php else: ?>
                                <span class="text-muted small">No file</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($prescriptions)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">No prescriptions yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
