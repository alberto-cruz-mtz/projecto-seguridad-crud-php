<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Middleware;

use Router\Contracts\MiddlewareInterface;
use Router\Request;
use Router\Response;
use Tito\CrudUsers\Service\Session\SessionManager;

final class RoleMiddleware implements MiddlewareInterface
{
    /** @var string[] */
    private array $allowedRoles;
    private SessionManager $sessionManager;

    /** @param string[] $allowedRoles */
    public function __construct(
        array $allowedRoles,
        ?SessionManager $sessionManager = null,
    ) {
        $this->sessionManager = $sessionManager ?? new SessionManager();
        $this->allowedRoles = array_map(
            static fn(string $role): string => strtolower(trim($role)),
            $allowedRoles,
        );
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $this->sessionManager->start();
        $authUser = $this->sessionManager->get('auth_user');

        if (!is_array($authUser)) {
            if ($this->isApiRequest($request)) {
                $response->jsonError('Sesion no iniciada.', 401);
                return;
            }

            $response->redirect('/login');
            return;
        }

        $roleName = strtolower((string) ($authUser['role']['name'] ?? ''));
        if (!in_array($roleName, $this->allowedRoles, true)) {
            if ($this->isApiRequest($request)) {
                $response->jsonError('Acceso denegado para este rol.', 403);
                return;
            }

            $response->redirect('/');
            return;
        }

        $next($request);
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
