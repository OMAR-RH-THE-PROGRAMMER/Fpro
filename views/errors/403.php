<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>403 Forbidden — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/dist/css/adminlte.min.css">
</head>
<body class="hold-transition">
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            <div class="error-page">
                <h2 class="headline text-warning">403</h2>
                <div class="error-content">
                    <h3><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Access Denied</h3>
                    <p>You do not have permission to access this page.</p>
                    <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="btn btn-warning">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>
