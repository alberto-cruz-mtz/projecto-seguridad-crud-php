<?php

namespace Tito\App\Service;

use RuntimeException;

class SessionService implements SessionServiceInterface
{
    private const SESSION_USER_KEY = 'authenticated_user';
    private const SESSION_COOKIE_LIFETIME = 0;
    private const SESSION_COOKIE_SECURE   = false;
    private const SESSION_COOKIE_HTTPONLY = true;
    private const SESSION_COOKIE_SAMESITE = 'Strict';

    public function startAuthenticatedSession(array $authenticatedUser): void
    {
        $this->configureSecureCookieParameters();
        $this->initializeSessionIfNotStarted();
        $this->regenerateSessionIdToPreventFixation();

        $_SESSION[self::SESSION_USER_KEY] = $authenticatedUser;
    }

    public function destroySession(): void
    {
        $this->initializeSessionIfNotStarted();

        $_SESSION = [];
        $this->deleteCookieIfExists();
        session_destroy();
    }

    public function hasActiveSession(): bool
    {
        $this->initializeSessionIfNotStarted();

        return isset($_SESSION[self::SESSION_USER_KEY]);
    }

    public function getAuthenticatedUser(): ?array
    {
        if (!$this->hasActiveSession()) {
            return null;
        }

        return $_SESSION[self::SESSION_USER_KEY];
    }

    // -------------------------------------------------------------------------
    // Métodos privados de soporte
    // -------------------------------------------------------------------------

    private function configureSecureCookieParameters(): void
    {
        session_set_cookie_params([
            'lifetime' => self::SESSION_COOKIE_LIFETIME,
            'path'     => '/',
            'secure'   => self::SESSION_COOKIE_SECURE,
            'httponly' => self::SESSION_COOKIE_HTTPONLY,
            'samesite' => self::SESSION_COOKIE_SAMESITE,
        ]);
    }

    private function initializeSessionIfNotStarted(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $sessionStarted = session_start();

        if (!$sessionStarted) {
            throw new RuntimeException('No se pudo iniciar la sesión PHP.');
        }
    }

    private function regenerateSessionIdToPreventFixation(): void
    {
        $idRegenerated = session_regenerate_id(true);

        if (!$idRegenerated) {
            throw new RuntimeException('No se pudo regenerar el ID de sesión.');
        }
    }

    private function deleteCookieIfExists(): void
    {
        $cookieName = session_name();

        if (!isset($_COOKIE[$cookieName])) {
            return;
        }

        setcookie($cookieName, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => self::SESSION_COOKIE_SECURE,
            'httponly' => self::SESSION_COOKIE_HTTPONLY,
            'samesite' => self::SESSION_COOKIE_SAMESITE,
        ]);
    }
}
