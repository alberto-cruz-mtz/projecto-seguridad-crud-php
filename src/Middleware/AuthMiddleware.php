<?php

namespace Tito\App\Middleware;

use Router\Contracts\MiddlewareInterface;
use Router\Request;
use Router\Response;
use Tito\App\Service\SessionServiceInterface;

readonly class AuthMiddleware implements MiddlewareInterface
{
    private const REDIRECT_PATH_ON_UNAUTHENTICATED = '/';

    public function __construct(private SessionServiceInterface $sessionService)
    {
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        if (!$this->sessionService->hasActiveSession()) {
            $response->redirect(self::REDIRECT_PATH_ON_UNAUTHENTICATED, 302);
            return;
        }

        $next($request, $response);
    }
}
