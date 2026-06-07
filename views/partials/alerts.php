<?php
$flash = getFlash();
if ($flash):
    $type = $flash['type'] ?? 'info';
    $msg  = $flash['message'] ?? '';
?>
<div class="alert alert-<?= e($type) ?> alert-dismissible fade show mx-3 mt-3" role="alert">
    <?= e($msg) ?>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
<?php endif; ?>
