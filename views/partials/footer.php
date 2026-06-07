</div><!-- /.wrapper -->

<script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/chart.js/Chart.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/adminlte/dist/js/adminlte.min.js"></script>
<script>
$(function () {
    if ($.fn.DataTable) {
        $('.data-table').DataTable({ "paging": false, "searching": true, "ordering": true });
    }
});
</script>
<?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
