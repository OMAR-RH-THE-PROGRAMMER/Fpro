<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/Paginator.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/SpecializationModel.php';

class UserController {

    public function index(): void {
        Auth::requireRole('admin');
        $model  = new UserModel();
        $page   = max(1, (int) ($_GET['p'] ?? 1));
        $role   = $_GET['role'] ?? '';
        $search = trim($_GET['search'] ?? '');

        $total = $model->countAll($role, $search);
        $pager = new Paginator($total, ITEMS_PER_PAGE, $page);
        $users = $model->getAllPaginated($page, $role, $search);

        $pageTitle = 'Manage Users — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/index.php';
    }

    public function create(): void {
        Auth::requireRole('admin');
        $specs     = (new SpecializationModel())->getAll();
        $pageTitle = 'Create User — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/create.php';
    }

    public function store(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('users', 'create'));
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'patient';
        $phone    = trim($_POST['phone'] ?? '');

        if (!$name || !$email || !$password) {
            setFlash('danger', 'Name, email and password are required.');
            redirect(url('users', 'create'));
        }

        $userModel = new UserModel();
        if ($userModel->findByEmail($email)) {
            setFlash('danger', 'Email already exists.');
            redirect(url('users', 'create'));
        }

        $hash   = password_hash($password, PASSWORD_BCRYPT);
        $userId = $userModel->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $hash,
            'role'     => $role,
            'phone'    => $phone,
        ]);

        // If creating a doctor, also fill doctors table
        if ($role === 'doctor') {
            $specId = (int) ($_POST['specialization_id'] ?? 0);
            $fee    = (float) ($_POST['consultation_fee'] ?? 0);
            $days   = $_POST['available_days'] ?? ['Sun', 'Mon', 'Tue', 'Wed', 'Thu'];
            $bio    = trim($_POST['bio'] ?? '');

            (new DoctorModel())->create([
                'user_id'          => $userId,
                'specialization_id' => $specId,
                'consultation_fee' => $fee,
                'available_days'   => implode(',', (array) $days),
                'bio'              => $bio,
            ]);
        }

        setFlash('success', 'User created successfully.');
        redirect(url('users'));
    }

    public function edit(): void {
        Auth::requireRole('admin');
        $id   = (int) ($_GET['id'] ?? 0);
        $model = new UserModel();
        $user  = $model->findById($id);
        if (!$user) {
            setFlash('danger', 'User not found.');
            redirect(url('users'));
        }
        $pageTitle = 'Edit User — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/edit.php';
    }

    public function update(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('users'));
        }

        $id    = (int) ($_POST['id'] ?? 0);
        $model = new UserModel();
        $user  = $model->findById($id);
        if (!$user) {
            setFlash('danger', 'User not found.');
            redirect(url('users'));
        }

        // Handle avatar upload
        $avatar = $user['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $file = $_FILES['avatar'];
            if ($file['size'] > MAX_AVATAR_SIZE) {
                setFlash('danger', 'Avatar too large (max 1MB).');
                redirect(url('users', 'edit', ['id' => $id]));
            }
            $info = getimagesize($file['tmp_name']);
            if (!$info) {
                setFlash('danger', 'Invalid image file.');
                redirect(url('users', 'edit', ['id' => $id]));
            }
            $ext    = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fname  = 'avatar_' . $id . '_' . time() . '.' . $ext;
            $dest   = UPLOAD_PATH . 'avatars/' . $fname;
            move_uploaded_file($file['tmp_name'], $dest);
            $avatar = $fname;
        }

        $model->update($id, [
            'name'   => trim($_POST['name'] ?? ''),
            'phone'  => trim($_POST['phone'] ?? ''),
            'avatar' => $avatar,
        ]);

        setFlash('success', 'User updated.');
        redirect(url('users'));
    }

    public function toggleActive(): void {
        Auth::requireRole('admin');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('users'));
        }

        $id      = (int) ($_POST['id'] ?? 0);
        $current = Auth::currentUser();
        if ($id === (int) $current['id']) {
            setFlash('danger', 'You cannot deactivate your own account.');
            redirect(url('users'));
        }

        (new UserModel())->toggleActive($id);
        setFlash('success', 'Account status updated.');
        redirect(url('users'));
    }

    public function profile(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        $user  = Auth::currentUser();
        $model = new UserModel();
        $data  = $model->findById($user['id']);
        $pageTitle = 'My Profile — ' . APP_NAME;
        require_once __DIR__ . '/../views/users/profile.php';
    }

    public function updateProfile(): void {
        Auth::requireRole('admin', 'doctor', 'patient');
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Invalid token.');
            redirect(url('users', 'profile'));
        }

        $user  = Auth::currentUser();
        $model = new UserModel();
        $data  = $model->findById($user['id']);

        $avatar = $data['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $file = $_FILES['avatar'];
            if ($file['size'] > MAX_AVATAR_SIZE) {
                setFlash('danger', 'Avatar too large (max 1MB).');
                redirect(url('users', 'profile'));
            }
            $info = getimagesize($file['tmp_name']);
            if (!$info) {
                setFlash('danger', 'Invalid image file.');
                redirect(url('users', 'profile'));
            }
            $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fname = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], UPLOAD_PATH . 'avatars/' . $fname);
            $avatar = $fname;
        }

        $model->update($user['id'], [
            'name'   => trim($_POST['name'] ?? ''),
            'phone'  => trim($_POST['phone'] ?? ''),
            'avatar' => $avatar,
        ]);

        // Password change
        $newPass = $_POST['new_password'] ?? '';
        if ($newPass !== '') {
            if (!password_verify($_POST['current_password'] ?? '', $data['password'])) {
                setFlash('danger', 'Current password is incorrect.');
                redirect(url('users', 'profile'));
            }
            $model->updatePassword($user['id'], password_hash($newPass, PASSWORD_BCRYPT));
        }

        setFlash('success', 'Profile updated successfully.');
        redirect(url('users', 'profile'));
    }
}
