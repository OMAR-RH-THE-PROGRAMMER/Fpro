<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class ReportController {

    public function index(): void {
        Auth::requireRole('admin');

        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date'   => $_GET['end_date'] ?? '',
            'doctor_id'  => (int) ($_GET['doctor_id'] ?? 0) ?: '',
            'status'     => $_GET['status'] ?? '',
        ];

        $appointments = [];
        $error        = '';

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            if ($filters['start_date'] > $filters['end_date']) {
                $error = 'Start date must be before end date.';
            } else {
                $appointments = (new AppointmentModel())->getForReport($filters);
            }
        }

        // Export CSV
        if (isset($_GET['export']) && $_GET['export'] === 'csv' && empty($error)) {
            $this->exportCsv($appointments);
        }

        $doctors   = (new DoctorModel())->getAll();
        $pageTitle = 'Reports — ' . APP_NAME;
        require_once __DIR__ . '/../views/reports/index.php';
    }

    private function exportCsv(array $appointments): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="report_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Patient Name', 'Doctor Name', 'Specialization', 'Date', 'Time', 'Status', 'Reason']);
        foreach ($appointments as $row) {
            fputcsv($out, [
                $row['patient_name'],
                $row['doctor_name'],
                $row['specialization_name'],
                $row['appt_date'],
                $row['appt_time'],
                $row['status'],
                $row['reason'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }
}
