<?php

use Router\Router;
use Tito\App\Controller\AuthenticationController;
use Tito\App\Controller\WelcomeController;
use Tito\App\Core\Database;
use Tito\App\Middleware\AuthMiddleware;
use Tito\App\Service\SessionService;

require_once __DIR__ . "/../vendor/autoload.php";

$router = new Router();

$db = Database::getInstance([
    'host' => 'localhost',
    'port' => 5432,
    'dbname' => 'php_seguridad_desarrollo',
    'user' => 'postgres',
    'password' => '',
]);

$authMiddleware = new AuthMiddleware(new SessionService());
$controller = new AuthenticationController();

$router->get("/", [AuthenticationController::class, "login"]);
$router->post("/auth/login", [AuthenticationController::class, "authenticate"]);
$router->post("/auth/logout", [AuthenticationController::class, "logout"]);

$router->get("/dashboard", [WelcomeController::class, "showDashboard"], [$authMiddleware]);

$router->dispatch();
