<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Controller;

use Router\Request;
use Router\Response;
final class StudentDashboardController
{
    public function view(Request $request, Response $response): void
    {
        $viewPath = $this->resolveViewPath('student-dashboard.html');
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
