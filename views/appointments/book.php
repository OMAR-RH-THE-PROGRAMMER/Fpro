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
                <div class="col-sm-6"><h1>Book Appointment</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('appointments') ?>">Appointments</a></li>
                        <li class="breadcrumb-item active">Book</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-plus mr-2"></i>New Appointment</h3></div>
                <form method="POST" action="<?= url('appointments','store') ?>">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Doctor *</label>
                                    <select name="doctor_id" id="doctorSelect" class="form-control" required>
                                        <option value="">— Choose a Doctor —</option>
                                        <?php foreach ($doctors as $d): ?>
                                        <option value="<?= (int)$d['id'] ?>"
                                                data-days="<?= e($d['available_days']) ?>"
                                                data-fee="<?= number_format($d['consultation_fee'],2) ?>"
                                                data-spec="<?= e($d['specialization_name']) ?>">
                                            Dr. <?= e($d['name']) ?> — <?= e($d['specialization_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- Doctor info box (shown after selection) -->
                                <div id="doctorInfo" class="callout callout-info" style="display:none">
                                    <h6 id="infoSpec"></h6>
                                    <p><i class="fas fa-calendar mr-1"></i> Available: <span id="infoDays"></span></p>
                                    <p><i class="fas fa-dollar-sign mr-1"></i> Fee: $<span id="infoFee"></span></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Appointment Date *</label>
                                    <input type="date" name="appt_date" id="apptDate" class="form-control" required
                                           min="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Time Slot *</label>
                                    <select name="appt_time" class="form-control" required>
                                        <option value="">— Pick time —</option>
                                        <?php foreach ($slots as $slot): ?>
                                        <option value="<?= $slot ?>"><?= formatTime($slot) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Reason for Visit</label>
                                    <input type="text" name="reason" class="form-control" placeholder="Brief description..." maxlength="255">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check mr-1"></i> Book Appointment
                        </button>
                        <a href="<?= url('appointments') ?>" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php
$extraJs = <<<JS
<script>
document.getElementById('doctorSelect').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (this.value) {
        document.getElementById('infoSpec').textContent = opt.dataset.spec;
        document.getElementById('infoDays').textContent = opt.dataset.days;
        document.getElementById('infoFee').textContent  = opt.dataset.fee;
        document.getElementById('doctorInfo').style.display = 'block';
    } else {
        document.getElementById('doctorInfo').style.display = 'none';
    }
});
</script>
JS;
require_once __DIR__ . '/../partials/footer.php'; ?>
