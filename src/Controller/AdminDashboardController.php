<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Controller;

use Router\Request;
use Router\Response;
use Tito\CrudUsers\Service\Session\SessionManager;

final class AdminDashboardController
{
    public function home(Request $request, Response $response): void
    {
        $sessionManager = new SessionManager();
        $sessionManager->start();

        $authUser = $sessionManager->get('auth_user');
        $roleName = strtolower((string) ($authUser['role']['name'] ?? ''));

        if ($roleName === 'admin') {
            $response->redirect('/admin/dashboard');
            return;
        }

        if ($roleName === 'treasurer') {
            $response->redirect('/treasurer/dashboard');
            return;
        }

        if ($roleName === 'student') {
            $response->redirect('/student/dashboard');
            return;
        }

        $response->redirect('/login?error=invalid_role');
    }

    public function view(Request $request, Response $response): void
    {
        $viewPath = $this->resolveViewPath('admin-dashboard.html');
        if ($viewPath === null) {
            $response->jsonError('Vista de dashboard no encontrada.', 404);
            return;
        }

        $response->view($viewPath);
    }

    private function resolveViewPath(string $viewName): ?string
    {
        $viewPath = dirname(__DIR__, 2) . '/resources/views/' . $viewName;
        if (!is_file($viewPath)) {
            return null;
        }

        return $viewPath;
    }
}
