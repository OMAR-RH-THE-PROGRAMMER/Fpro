<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';

class DoctorController {

    public function index(): void {
        Auth::requireRole('admin');
        $model = new DoctorModel();
        $page  = max(1, (int) ($_GET['p'] ?? 1));
        $total = $model->countAll();
        $pager   = new Paginator($total, ITEMS_PER_PAGE, $page);
        $doctors = $model->getAllPaginated($page);

        $pageTitle = 'Manage Doctors — ' . APP_NAME;
        require_once __DIR__ . '/../views/doctors/index.php';
    }

    public function edit(): void {
        Auth::requireRole('admin', 'doctor');
        $id = (int) ($_GET['id'] ?? 0);

        // Doctor can only edit their own profile
        if (Auth::role() === 'doctor') {
            $docModel = new DoctorModel();
            $doc      = $docModel->findByUserId(Auth::currentUser()['id']);
            if (!$doc || $doc['id'] !== $id) {
                redirect(url('error', '403'));
            }
        }

        $model  = new DoctorModel();
        $doctor = $model->findById($id);
        if (!$doctor) {
            setFlash('danger', 'Doctor not found.');
            redirect(url('doctors'));
        }

        $specs     = (new SpecializationModel())->getAll();
        $pageTitle = 'Edit Doctor — ' . APP_NAME;
        require_once __DIR__ . '/../views/doctors/edit.php';
    }

    public function update(): void {
        Auth::requireRole('admin', 'doctor');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('doctors'));
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $model  = new DoctorModel();
        $doctor = $model->findById($id);
        if (!$doctor) {
            setFlash('danger', 'Doctor not found.');
            redirect(url('doctors'));
        }

        // Ownership check for doctor role
        if (Auth::role() === 'doctor') {
            $myDoc = $model->findByUserId(Auth::currentUser()['id']);
            if (!$myDoc || $myDoc['id'] !== $id) {
                redirect(url('error', '403'));
            }
        }

        $days = $_POST['available_days'] ?? [];

        // Photo upload
        $avatar = $doctor['avatar'];
        if (!empty($_FILES['photo']['name'])) {
            $file = $_FILES['photo'];
            if ($file['size'] > MAX_AVATAR_SIZE) {
                setFlash('danger', 'Photo too large (max 1MB).');
                redirect(url('doctors', 'edit', ['id' => $id]));
            }
            $info = getimagesize($file['tmp_name']);
            if (!$info) {
                setFlash('danger', 'Invalid image.');
                redirect(url('doctors', 'edit', ['id' => $id]));
            }
            $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fname = 'doctor_' . $id . '_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], UPLOAD_PATH . 'doctor_photos/' . $fname);
            $avatar = $fname;
            // Update user avatar
            require_once __DIR__ . '/../models/UserModel.php';
            $uModel = new UserModel();
            $uData  = $uModel->findById($doctor['user_id']);
            $uModel->update($doctor['user_id'], ['name' => $uData['name'], 'phone' => $uData['phone'], 'avatar' => $avatar]);
        }

        $model->update($id, [
            'specialization_id' => (int) ($_POST['specialization_id'] ?? 0),
            'bio'               => trim($_POST['bio'] ?? ''),
            'consultation_fee'  => (float) ($_POST['consultation_fee'] ?? 0),
            'available_days'    => implode(',', (array) $days),
        ]);

        setFlash('success', 'Doctor profile updated.');
        if (Auth::role() === 'doctor') {
            redirect(url('dashboard'));
        }
        redirect(url('doctors'));
    }

    public function specializations(): void {
        Auth::requireRole('admin');
        $specs     = (new SpecializationModel())->getAll();
        $pageTitle = 'Specializations — ' . APP_NAME;
        require_once __DIR__ . '/../views/doctors/specializations.php';
    }

    public function addSpecialization(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('doctors', 'specializations'));
        }
        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            setFlash('danger', 'Name is required.');
            redirect(url('doctors', 'specializations'));
        }
        (new SpecializationModel())->create($name);
        setFlash('success', 'Specialization added.');
        redirect(url('doctors', 'specializations'));
    }

    public function deleteSpecialization(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('doctors', 'specializations'));
        }
        $id    = (int) ($_POST['id'] ?? 0);
        $model = new SpecializationModel();
        if (!$model->isSafeToDelete($id)) {
            setFlash('danger', 'Cannot delete: doctors are assigned to this specialization.');
            redirect(url('doctors', 'specializations'));
        }
        $model->delete($id);
        setFlash('success', 'Specialization deleted.');
        redirect(url('doctors', 'specializations'));
    }
}
