<?php

namespace Tito\App\Controller;

use Router\Request;
use Router\Response;
use Tito\App\Service\SessionService;

readonly class WelcomeController
{
    private SessionService $sessionService;

    public function __construct()
    {
        $this->sessionService = new SessionService();
    }

    public function showDashboard(Request $req, Response $res): void
    {
        $authenticatedUser = $this->sessionService->getAuthenticatedUser();

        $res->view(__DIR__ . '/../View/welcome.php', [
            'displayName' => $authenticatedUser['displayName'],
            'email'       => $authenticatedUser['email'],
            'role'        => $authenticatedUser['role'],
        ]);
    }
}
