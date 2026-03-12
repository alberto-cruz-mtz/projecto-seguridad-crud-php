<?php

namespace Tito\App\Controller;

use Router\Request;
use Router\Response;
use Tito\App\Repository\PostgresUserRepository;
use Tito\App\Service\AuthenticationService;
use Tito\App\Service\SessionService;

readonly class AuthenticationController
{
    private AuthenticationService $authenticationService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $this->authenticationService = new AuthenticationService(
            new PostgresUserRepository(),
            $sessionService
        );
    }

    public function login(Request $req, Response $res): void
    {
        $res->view(__DIR__ . "/../View/login.html");
    }

    public function authenticate(Request $req, Response $res): void
    {
        $email    = $req->body("username");
        $password = $req->body("password");

        $result = $this->authenticationService->authenticate($email, $password);

        if (isset($result['error'])) {
            $res->json($result, 401);
            return;
        }

        $res->json($result);
    }

    public function logout(Request $req, Response $res): void
    {
        $this->authenticationService->logout();

        $res->redirect('/', 302);
    }
}
