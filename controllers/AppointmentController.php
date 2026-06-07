<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';

class AppointmentController {

    public function index(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $user  = Auth::currentUser();
        $role  = Auth::role();
        $model = new AppointmentModel();
        $page  = max(1, (int) ($_GET['p'] ?? 1));

        $filters = [
            'status'       => $_GET['status'] ?? '',
            'doctor_id'    => (int) ($_GET['doctor_id'] ?? 0) ?: '',
            'patient_name' => trim($_GET['patient_name'] ?? ''),
            'start_date'   => $_GET['start_date'] ?? '',
            'end_date'     => $_GET['end_date'] ?? '',
        ];

        if ($role === 'patient') {
            $total = $model->countFiltered('patient', $user['id'], $filters);
            $pager = new Paginator($total, ITEMS_PER_PAGE, $page);
            $appointments = $model->getByPatient($user['id'], $page, $filters);
        } elseif ($role === 'doctor') {
            $docModel = new DoctorModel();
            $doctor   = $docModel->findByUserId($user['id']);
            $total    = $model->countFiltered('doctor', $doctor['id'], $filters);
            $pager    = new Paginator($total, ITEMS_PER_PAGE, $page);
            $appointments = $model->getByDoctor($doctor['id'], $page, $filters);
        } else {
            $total = $model->countFiltered('all', 0, $filters);
            $pager = new Paginator($total, ITEMS_PER_PAGE, $page);
            $appointments = $model->getAll($page, $filters);
        }

        $doctors   = (new DoctorModel())->getAll();
        $pageTitle = 'Appointments — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/index.php';
    }

    public function book(): void {
        Auth::requireRole('patient');
        $doctors   = (new DoctorModel())->getAll();
        $slots     = generateTimeSlots();
        $pageTitle = 'Book Appointment — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/book.php';
    }

    public function store(): void {
        Auth::requireRole('patient');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('appointments', 'book'));
        }

        $user     = Auth::currentUser();
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $date     = $_POST['appt_date'] ?? '';
        $time     = $_POST['appt_time'] ?? '';
        $reason   = trim($_POST['reason'] ?? '');

        // Validate date is not in the past
        if ($date < date('Y-m-d')) {
            setFlash('danger', 'Appointment date cannot be in the past.');
            redirect(url('appointments', 'book'));
        }

        // Validate day of week matches doctor's available days
        $docModel = new DoctorModel();
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dayOfWeek = $dayNames[(int) date('w', strtotime($date))];
        $availDays = $docModel->getAvailableDays($doctorId);

        if (!in_array($dayOfWeek, $availDays, true)) {
            setFlash('danger', 'Doctor is not available on this day.');
            redirect(url('appointments', 'book'));
        }

        $apptModel = new AppointmentModel();
        if ($apptModel->hasConflict($doctorId, $date, $time)) {
            setFlash('danger', 'This slot is already booked. Please choose another time.');
            redirect(url('appointments', 'book'));
        }

        $apptModel->book([
            'patient_id' => $user['id'],
            'doctor_id'  => $doctorId,
            'appt_date'  => $date,
            'appt_time'  => $time,
            'reason'     => $reason,
        ]);

        setFlash('success', 'Appointment booked successfully!');
        redirect(url('appointments'));
    }

    public function view(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $id   = (int) ($_GET['id'] ?? 0);
        $appt = (new AppointmentModel())->findById($id);

        if (!$appt) {
            setFlash('danger', 'Appointment not found.');
            redirect(url('appointments'));
        }

        // Ownership check
        $user = Auth::currentUser();
        $role = Auth::role();
        if ($role === 'patient' && (int) $appt['patient_id'] !== $user['id']) {
            redirect(url('error', '403'));
        }
        if ($role === 'doctor') {
            $doc = (new DoctorModel())->findByUserId($user['id']);
            if (!$doc || (int) $appt['doctor_id'] !== (int) $doc['id']) {
                redirect(url('error', '403'));
            }
        }

        $pageTitle = 'Appointment Details — ' . APP_NAME;
        require_once __DIR__ . '/../views/appointments/view.php';
    }

    public function updateStatus(): void {
        Auth::requireRole('admin', 'doctor');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('appointments'));
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $notes  = trim($_POST['doctor_notes'] ?? '');
        $model  = new AppointmentModel();
        $appt   = $model->findById($id);

        if (!$appt) {
            setFlash('danger', 'Appointment not found.');
            redirect(url('appointments'));
        }

        // Doctor ownership check
        if (Auth::role() === 'doctor') {
            $doc = (new DoctorModel())->findByUserId(Auth::currentUser()['id']);
            if (!$doc || (int) $appt['doctor_id'] !== (int) $doc['id']) {
                redirect(url('error', '403'));
            }
        }

        $model->updateStatus($id, $status, $notes);
        setFlash('success', 'Appointment status updated.');
        redirect(url('appointments', 'view', ['id' => $id]));
    }

    public function cancel(): void {
        Auth::requireRole('patient');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('appointments'));
        }

        $id   = (int) ($_POST['id'] ?? 0);
        $user = Auth::currentUser();
        $model = new AppointmentModel();
        $appt  = $model->findById($id);

        if (!$appt || (int) $appt['patient_id'] !== $user['id']) {
            setFlash('danger', 'Not allowed.');
            redirect(url('appointments'));
        }

        if ($appt['status'] !== 'pending') {
            setFlash('danger', 'Only pending appointments can be cancelled.');
            redirect(url('appointments'));
        }

        $model->updateStatus($id, 'cancelled');
        setFlash('success', 'Appointment cancelled.');
        redirect(url('appointments'));
    }
}
