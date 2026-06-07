<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';

class DashboardController {

    public function index(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $role = Auth::role();

        if ($role === 'admin') {
            $this->adminDashboard();
        } elseif ($role === 'doctor') {
            $this->doctorDashboard();
        } else {
            $this->patientDashboard();
        }
    }

    private function adminDashboard(): void {
        $userModel  = new UserModel();
        $apptModel  = new AppointmentModel();

        $usersByRole    = $userModel->countByRole();
        $todayCount     = $apptModel->countToday();
        $weekByStatus   = $apptModel->countByStatusThisWeek();
        $recentAppts    = $apptModel->getRecent(5);
        $chartData      = $apptModel->getLast14DaysStats();

        $pageTitle = 'Admin Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/admin.php';
    }

    private function doctorDashboard(): void {
        $user      = Auth::currentUser();
        $docModel  = new DoctorModel();
        $apptModel = new AppointmentModel();

        $doctor    = $docModel->findByUserId($user['id']);
        if (!$doctor) {
            setFlash('warning', 'Doctor profile not found.');
            redirect(url('auth', 'login'));
        }

        $todayAppts  = $apptModel->getTodayByDoctor($doctor['id']);
        $monthCount  = $apptModel->countThisMonth($doctor['id']);
        $upcoming    = $apptModel->getUpcoming($doctor['id'], 5);

        $pageTitle = 'Doctor Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/doctor.php';
    }

    private function patientDashboard(): void {
        $user      = Auth::currentUser();
        $apptModel = new AppointmentModel();
        $prescModel = new PrescriptionModel();

        $stats     = $apptModel->getPatientStats($user['id']);
        $nextAppt  = $apptModel->getNextForPatient($user['id']);
        $prescCount = $prescModel->countByPatient($user['id']);

        $pageTitle = 'My Dashboard — ' . APP_NAME;
        require_once __DIR__ . '/../views/dashboard/patient.php';
    }
}
