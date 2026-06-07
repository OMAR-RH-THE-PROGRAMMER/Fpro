<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/CSRF.php';
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
$availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1>Edit Doctor — <?= e($doctor['name']) ?></h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>
            <div class="card card-warning">
                <div class="card-header"><h3 class="card-title">Doctor Profile</h3></div>
                <form method="POST" action="<?= url('doctors','update') ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <input type="hidden" name="id" value="<?= (int)$doctor['id'] ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Specialization</label>
                                    <select name="specialization_id" class="form-control" required>
                                        <?php foreach ($specs as $s): ?>
                                        <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === (int)$doctor['specialization_id'] ? 'selected' : '' ?>>
                                            <?= e($s['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Consultation Fee ($)</label>
                                    <input type="number" name="consultation_fee" class="form-control" step="0.01"
                                           value="<?= number_format($doctor['consultation_fee'],2) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Profile Photo (JPEG/PNG, max 1MB)</label>
                                    <input type="file" name="photo" class="form-control-file" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Available Days</label><br>
                            <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day): ?>
                            <div class="icheck-primary d-inline mr-3">
                                <input type="checkbox" name="available_days[]" value="<?= $day ?>" id="day_<?= $day ?>"
                                       <?= in_array($day, $availDays) ? 'checked' : '' ?>>
                                <label for="day_<?= $day ?>"><?= $day ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-group">
                            <label>Bio</label>
                            <textarea name="bio" class="form-control" rows="4"><?= e($doctor['bio']??'') ?></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Update</button>
                        <a href="<?= url('doctors') ?>" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
