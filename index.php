<?php
/**
 * ClinicDesk — Front Controller
 * All requests are routed here via .htaccess
 */

// Suppress display errors in production — log them instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Start session early
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load core files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/CSRF.php';
require_once __DIR__ . '/core/Paginator.php';
require_once __DIR__ . '/core/helpers.php';

// Read routing parameters
$page   = $_GET['page']   ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Route map: page => [controller file, class name]
$routes = [
    'auth'          => ['controllers/AuthController.php',         'AuthController'],
    'dashboard'     => ['controllers/DashboardController.php',    'DashboardController'],
    'users'         => ['controllers/UserController.php',         'UserController'],
    'doctors'       => ['controllers/DoctorController.php',       'DoctorController'],
    'appointments'  => ['controllers/AppointmentController.php',  'AppointmentController'],
    'prescriptions' => ['controllers/PrescriptionController.php', 'PrescriptionController'],
    'reports'       => ['controllers/ReportController.php',       'ReportController'],
    'error'         => [null, null],
];

// Special error pages
if ($page === 'error') {
    require_once __DIR__ . '/config/config.php';
    $code = $action === '403' ? '403' : '404';
    require_once __DIR__ . "/views/errors/{$code}.php";
    exit;
}

if (!isset($routes[$page])) {
    require_once __DIR__ . '/views/errors/404.php';
    exit;
}

[$controllerFile, $controllerClass] = $routes[$page];

if (!file_exists(__DIR__ . '/' . $controllerFile)) {
    require_once __DIR__ . '/views/errors/404.php';
    exit;
}

require_once __DIR__ . '/' . $controllerFile;
$controller = new $controllerClass();

// Map action string to method name (convert snake_case / special chars safely)
// Allowed action->method map
$actionMap = [
    // Auth
    'login'         => 'login',
    'login_post'    => 'handleLogin',
    'logout'        => 'handleLogout',
    // Dashboard
    'index'         => 'index',
    // Users
    'create'        => 'create',
    'store'         => 'store',
    'edit'          => 'edit',
    'update'        => 'update',
    'toggle_active' => 'toggleActive',
    'profile'       => 'profile',
    'update_profile'=> 'updateProfile',
    // Doctors
    'specializations'       => 'specializations',
    'add_specialization'    => 'addSpecialization',
    'delete_specialization' => 'deleteSpecialization',
    // Appointments
    'book'          => 'book',
    'view'          => 'view',
    'update_status' => 'updateStatus',
    'cancel'        => 'cancel',
    // Prescriptions
    'add'           => 'add',
    'download'      => 'download',
];

// Default action
if ($action === '' || $action === 'index') {
    $method = 'index';
} else {
    $method = $actionMap[$action] ?? null;
}

if (!$method || !method_exists($controller, $method)) {
    require_once __DIR__ . '/views/errors/404.php';
    exit;
}

try {
    $controller->$method();
} catch (RuntimeException $e) {
    error_log('Runtime error: ' . $e->getMessage());
    echo '<div style="background:#f8d7da;color:#721c24;padding:20px;font-family:sans-serif">
        <h3>Something went wrong</h3>
        <p>Please try again or contact the administrator.</p>
      </div>';
}
