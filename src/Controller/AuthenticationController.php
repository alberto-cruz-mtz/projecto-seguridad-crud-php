<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Controller;

use JsonException;
use Router\Request;
use Router\Response;
use Throwable;
use Tito\CrudUsers\Core\Application;
use Tito\CrudUsers\DTO\LoginRequestDTO;
use Tito\CrudUsers\DTO\PasswordResetConfirmDTO;
use Tito\CrudUsers\DTO\PasswordResetRequestDTO;
use Tito\CrudUsers\Exception\AuthenticationException;
use Tito\CrudUsers\Exception\NotFoundException;
use Tito\CrudUsers\Exception\ValidationException;
use Tito\CrudUsers\Service\Session\SessionManager;

final class AuthenticationController
{
    private SessionManager $sessionManager;

    public function __construct()
    {
        $this->sessionManager = new SessionManager();
    }

    public function loginView(Request $request, Response $response): void
    {
        $viewPath = $this->resolveViewPath('login.html');
        if ($viewPath === null) {
            $response->jsonError('Vista de login no encontrada.', 404);
            return;
        }

        $response->view($viewPath);
    }

    public function login(Request $request, Response $response): void
    {
        try {
            $payload = $this->extractPayload($request);
            $dto = LoginRequestDTO::fromArray($payload);
            $sessionUser = Application::authenticationService()->login($dto);

            $response->json([
                'message' => 'Autenticacion exitosa.',
                'user' => $sessionUser,
            ]);
        } catch (ValidationException $e) {
            $response->jsonError($e->getMessage(), 422, $e->errors());
        } catch (AuthenticationException $e) {
            $response->jsonError($e->getMessage(), 401);
        } catch (Throwable $e) {
            $response->jsonError('Error interno del servidor.', 500, ['reason' => $e->getMessage()]);
        }
    }

    public function requestPasswordReset(Request $request, Response $response): void
    {
        try {
            $payload = $this->extractPayload($request);
            $dto = PasswordResetRequestDTO::fromArray($payload);
            $result = Application::authenticationService()->requestPasswordReset($dto);

            $response->json($result);
        } catch (ValidationException $e) {
            $response->jsonError($e->getMessage(), 422, $e->errors());
        } catch (NotFoundException $e) {
            $response->jsonError($e->getMessage(), 404);
        } catch (Throwable $e) {
            $response->jsonError('Error interno del servidor.', 500, ['reason' => $e->getMessage()]);
        }
    }

    public function passwordResetRequestView(Request $request, Response $response): void
    {
        $viewPath = $this->resolveViewPath('password-reset-request.html');
        if ($viewPath === null) {
            $response->jsonError('Vista de solicitud de restablecimiento no encontrada.', 404);
            return;
        }

        $response->view($viewPath);
    }

    public function passwordResetView(Request $request, Response $response): void
    {
        $isValidToken = Application::authenticationService()->validateResetTokenFromCookie();
        if (!$isValidToken) {
            $response->redirect('/password-reset/request?error=invalid_token');
            return;
        }

        $viewPath = $this->resolveViewPath('password-reset.html');
        if ($viewPath === null) {
            $response->redirect('/password-reset/request?error=view_not_found');
            return;
        }

        $response->view($viewPath);
    }

    public function confirmPasswordReset(Request $request, Response $response): void
    {
        try {
            $payload = $this->extractPayload($request);
            $dto = PasswordResetConfirmDTO::fromArray($payload);
            $result = Application::authenticationService()->confirmPasswordReset($dto);

            $response->json($result);
        } catch (ValidationException $e) {
            $response->jsonError($e->getMessage(), 422, $e->errors());
        } catch (AuthenticationException $e) {
            $response->jsonError($e->getMessage(), 401);
        } catch (NotFoundException $e) {
            $response->jsonError($e->getMessage(), 404);
        } catch (Throwable $e) {
            $response->jsonError('Error interno del servidor.', 500, ['reason' => $e->getMessage()]);
        }
    }

    public function me(Request $request, Response $response): void
    {
        $this->sessionManager->start();
        $authUser = $this->sessionManager->get('auth_user');
        if (!is_array($authUser)) {
            $response->jsonError('Sesion no iniciada.', 401);
            return;
        }

        $response->json(['user' => $authUser]);
    }

    public function logout(Request $request, Response $response): void
    {
        $this->sessionManager->start();

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $response->json(['message' => 'Sesion cerrada correctamente.']);
    }

    /** @return array<string, mixed> */
    private function extractPayload(Request $request): array
    {
        if (!$request->isJson()) {
            return $request->allBody();
        }

        try {
            return $request->json();
        } catch (JsonException) {
            return [];
        }
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
