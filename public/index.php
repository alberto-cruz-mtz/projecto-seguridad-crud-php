<?php

use Tito\App\Controller\AuthenticationController;
use Tito\App\Core\Router;

require_once __DIR__ . "/../vendor/autoload.php";

$router = new Router();

$router->get("/", [AuthenticationController::class, "login"]);

$router->dispatch();