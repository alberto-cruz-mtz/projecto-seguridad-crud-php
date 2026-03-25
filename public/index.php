<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Router\Router;
use Tito\CrudUsers\Core\Database;
use Tito\CrudUsers\Controller\AdminDashboardController;
use Tito\CrudUsers\Controller\AuthenticationController;
use Tito\CrudUsers\Controller\DashboardController;
use Tito\CrudUsers\Controller\StudentDashboardController;
use Tito\CrudUsers\Controller\UserController;
use Tito\CrudUsers\Middleware\AuthSessionMiddleware;
use Tito\CrudUsers\Middleware\RoleMiddleware;

$db = Database::getInstance([
    'host' => 'localhost',
    'port' => 5432,
    'dbname' => 'php_seguridad_desarrollo',
    'user' => 'postgres',
    'password' => '',
]);

$router = new Router();

$authMiddleware = new AuthSessionMiddleware();
$adminRoleMiddleware = new RoleMiddleware(['admin']);
$treasurerRoleMiddleware = new RoleMiddleware(['treasurer']);
$studentRoleMiddleware = new RoleMiddleware(['student']);

$router->get('/api/users', [UserController::class, 'index'], [$authMiddleware, $adminRoleMiddleware]);
$router->get('/api/users/:id', [UserController::class, 'show'], [$authMiddleware, $adminRoleMiddleware]);
$router->post('/api/users', [UserController::class, 'store'], [$authMiddleware, $adminRoleMiddleware]);
$router->put('/api/users/:id', [UserController::class, 'update'], [$authMiddleware, $adminRoleMiddleware]);
$router->delete('/api/users/:id', [UserController::class, 'destroy'], [$authMiddleware, $adminRoleMiddleware]);

$router->get('/login', [AuthenticationController::class, 'loginView']);
$router->post('/api/auth/login', [AuthenticationController::class, 'login']);
$router->get('/api/auth/me', [AuthenticationController::class, 'me'], [$authMiddleware]);
$router->post('/api/auth/logout', [AuthenticationController::class, 'logout'], [$authMiddleware]);
$router->post('/api/auth/password-reset/request', [AuthenticationController::class, 'requestPasswordReset']);
$router->post('/api/auth/password-reset/confirm', [AuthenticationController::class, 'confirmPasswordReset']);
$router->get('/password-reset/request', [AuthenticationController::class, 'passwordResetRequestView']);
$router->get('/password-reset', [AuthenticationController::class, 'passwordResetView']);

$router->get('/', [AdminDashboardController::class, 'home'], [$authMiddleware]);
$router->get('/admin/dashboard', [AdminDashboardController::class, 'view'], [$authMiddleware, $adminRoleMiddleware]);
$router->get('/treasurer/dashboard', [DashboardController::class, 'view'], [$authMiddleware, $treasurerRoleMiddleware]);
$router->get('/student/dashboard', [StudentDashboardController::class, 'view'], [$authMiddleware, $studentRoleMiddleware]);

$router->dispatch();
