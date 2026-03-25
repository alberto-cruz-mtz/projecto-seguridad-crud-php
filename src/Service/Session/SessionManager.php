<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Service\Session;

final class SessionManager
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $this->isSecureConnection(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public function regenerateId(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        session_regenerate_id(true);
    }

    public function set(string $key, mixed $value): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $default;
        }

        return $_SESSION[$key] ?? $default;
    }

    private function isSecureConnection(): bool
    {
        $https = $_SERVER['HTTPS'] ?? '';
        if ($https !== '' && strtolower((string) $https) !== 'off') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}
