<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../models/PrescriptionModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class PrescriptionController {

    public function index(): void {
        Auth::requireRole('patient');
        $user        = Auth::currentUser();
        $prescModel  = new PrescriptionModel();
        $prescriptions = $prescModel->getByPatient($user['id']);
        $pageTitle   = 'My Prescriptions — ' . APP_NAME;
        require_once __DIR__ . '/../views/prescriptions/index.php';
    }

    public function add(): void {
        Auth::requireRole('doctor');
        $apptId   = (int) ($_GET['appt_id'] ?? 0);
        $apptModel = new AppointmentModel();
        $appt      = $apptModel->findById($apptId);

        if (!$appt) {
            setFlash('danger', 'Appointment not found.');
            redirect(url('appointments'));
        }

        // Ownership + status check
        $doc = (new DoctorModel())->findByUserId(Auth::currentUser()['id']);
        if (!$doc || (int) $appt['doctor_id'] !== (int) $doc['id']) {
            redirect(url('error', '403'));
        }
        if ($appt['status'] !== 'completed') {
            setFlash('danger', 'Can only add prescription to completed appointments.');
            redirect(url('appointments', 'view', ['id' => $apptId]));
        }
        if ($appt['prescription_id']) {
            setFlash('warning', 'Prescription already exists.');
            redirect(url('appointments', 'view', ['id' => $apptId]));
        }

        $pageTitle = 'Add Prescription — ' . APP_NAME;
        require_once __DIR__ . '/../views/prescriptions/add.php';
    }

    public function store(): void {
        Auth::requireRole('doctor');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('appointments'));
        }

        $apptId    = (int) ($_POST['appointment_id'] ?? 0);
        $apptModel = new AppointmentModel();
        $appt      = $apptModel->findById($apptId);

        if (!$appt) {
            setFlash('danger', 'Appointment not found.');
            redirect(url('appointments'));
        }

        $doc = (new DoctorModel())->findByUserId(Auth::currentUser()['id']);
        if (!$doc || (int) $appt['doctor_id'] !== (int) $doc['id']) {
            redirect(url('error', '403'));
        }

        $filePath = null;
        if (!empty($_FILES['prescription_file']['name'])) {
            $file = $_FILES['prescription_file'];
            if ($file['size'] > MAX_PRESCRIPTION_SIZE) {
                setFlash('danger', 'File too large (max 3MB).');
                redirect(url('prescriptions', 'add', ['appt_id' => $apptId]));
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if ($mime !== 'application/pdf') {
                setFlash('danger', 'Only PDF files are allowed.');
                redirect(url('prescriptions', 'add', ['appt_id' => $apptId]));
            }
            $fname    = 'prescription_' . $apptId . '_' . time() . '.pdf';
            $dest     = UPLOAD_PATH . 'prescriptions/' . $fname;
            move_uploaded_file($file['tmp_name'], $dest);
            $filePath = $fname;
        }

        (new PrescriptionModel())->create([
            'appointment_id' => $apptId,
            'diagnosis'      => trim($_POST['diagnosis'] ?? ''),
            'medications'    => trim($_POST['medications'] ?? ''),
            'notes'          => trim($_POST['notes'] ?? ''),
            'file_path'      => $filePath,
        ]);

        setFlash('success', 'Prescription added.');
        redirect(url('appointments', 'view', ['id' => $apptId]));
    }

    public function download(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $id   = (int) ($_GET['id'] ?? 0);
        $prescModel = new PrescriptionModel();
        $presc      = $prescModel->findById($id);

        if (!$presc) {
            setFlash('danger', 'Prescription not found.');
            redirect(url('appointments'));
        }

        $user = Auth::currentUser();
        $role = Auth::role();

        // Ownership checks
        if ($role === 'patient' && (int) $presc['patient_id'] !== $user['id']) {
            redirect(url('error', '403'));
        }
        if ($role === 'doctor') {
            $doc = (new DoctorModel())->findByUserId($user['id']);
            if (!$doc || (int) $presc['doctor_id'] !== (int) $doc['id']) {
                redirect(url('error', '403'));
            }
        }

        if (!$presc['file_path']) {
            setFlash('danger', 'No file attached to this prescription.');
            redirect(url('prescriptions'));
        }

        $filePath = UPLOAD_PATH . 'prescriptions/' . $presc['file_path'];
        if (!file_exists($filePath)) {
            setFlash('danger', 'File not found on server.');
            redirect(url('prescriptions'));
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="prescription.pdf"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
