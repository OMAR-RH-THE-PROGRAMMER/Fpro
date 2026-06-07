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
                <div class="col-sm-6"><h1>Specializations</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('doctors') ?>">Doctors</a></li>
                        <li class="breadcrumb-item active">Specializations</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">Add Specialization</h3></div>
                        <form method="POST" action="<?= url('doctors','add_specialization') ?>">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" required placeholder="e.g. Cardiology">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus mr-1"></i> Add
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">All Specializations</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead><tr><th>#</th><th>Name</th><th>Action</th></tr></thead>
                                <tbody>
                                <?php foreach ($specs as $s): ?>
                                <tr>
                                    <td><?= (int)$s['id'] ?></td>
                                    <td><?= e($s['name']) ?></td>
                                    <td>
                                        <form method="POST" action="<?= url('doctors','delete_specialization') ?>" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                            <button type="submit" class="btn btn-xs btn-danger"
                                                    onclick="return confirm('Delete this specialization?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
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
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
