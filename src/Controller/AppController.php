<?php

namespace Tito\App\Controller;

use Router\Request;
use Router\Response;
use Tito\App\Service\SessionServiceInterface;

readonly class AppController
{
    public function showDashboard(Request $request, Response $response): void{

        $response->view(__DIR__ . "/../View/dashboard.html");
    }
}