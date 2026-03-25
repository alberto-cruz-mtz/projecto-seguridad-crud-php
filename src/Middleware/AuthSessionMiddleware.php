<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Middleware;

use Router\Contracts\MiddlewareInterface;
use Router\Request;
use Router\Response;
use Tito\CrudUsers\Service\Session\SessionManager;

final class AuthSessionMiddleware implements MiddlewareInterface
{
    private SessionManager $sessionManager;

    public function __construct(?SessionManager $sessionManager = null)
    {
        $this->sessionManager = $sessionManager ?? new SessionManager();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $this->sessionManager->start();
        $authUser = $this->sessionManager->get('auth_user');

        if (is_array($authUser)) {
            $next($request);
            return;
        }

        if ($this->isApiRequest($request)) {
            $response->jsonError('Sesion no iniciada.', 401);
            return;
        }

        $response->redirect('/login');
    }

    private function isApiRequest(Request $request): bool
    {
        if (str_starts_with($request->getPath(), '/api/')) {
            return true;
        }

        if ($request->isXhr()) {
            return true;
        }

        return str_contains(strtolower($request->header('accept', '')), 'application/json');
    }
}
