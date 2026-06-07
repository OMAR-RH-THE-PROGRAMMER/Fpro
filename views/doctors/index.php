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
                <div class="col-sm-6"><h1>Manage Doctors</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Doctors</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php require_once __DIR__ . '/../partials/alerts.php'; ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Doctors</h3>
                    <div class="card-tools">
                        <a href="<?= url('users','create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus mr-1"></i> Add Doctor (via Users)
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped data-table">
                        <thead>
                            <tr><th>Name</th><th>Specialization</th><th>Fee</th><th>Available Days</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($doctors as $d): ?>
                        <tr>
                            <td><?= e($d['name']) ?></td>
                            <td><?= e($d['specialization_name']) ?></td>
                            <td>$<?= number_format($d['consultation_fee'],2) ?></td>
                            <td><small><?= e($d['available_days']) ?></small></td>
                            <td>
                                <?php if ((int)$d['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('doctors','edit',['id'=>$d['id']]) ?>" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <!-- Pagination -->
                    <?php if ($pager->totalPages() > 1): ?>
                    <nav>
                        <ul class="pagination pagination-sm">
                            <?php for ($i = 1; $i <= $pager->totalPages(); $i++): ?>
                            <li class="page-item <?= $i == $pager->getCurrentPage() ? 'active' : '' ?>">
                                <a class="page-link" href="<?= url('doctors','',['p'=>$i]) ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
