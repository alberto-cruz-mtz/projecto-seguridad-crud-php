<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Core;

final class Config
{
    public static function database(): array
    {
        return [
            'host' => self::env('DB_HOST', '127.0.0.1'),
            'port' => self::env('DB_PORT', '5432'),
            'dbname' => self::env('DB_NAME', 'crud_users'),
            'user' => self::env('DB_USER', 'postgres'),
            'password' => self::env('DB_PASSWORD', 'postgres'),
        ];
    }

    public static function mail(): array
    {
        return [
            'host' => self::env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => (int) self::env('MAIL_PORT', '587'),
            'username' => self::env('MAIL_USERNAME', ''),
            'password' => self::env('MAIL_PASSWORD', ''),
            'encryption' => self::env('MAIL_ENCRYPTION', 'tls'),
            'from_address' => self::env('MAIL_FROM_ADDRESS', ''),
            'from_name' => self::env('MAIL_FROM_NAME', 'CRUD Users'),
        ];
    }

    public static function app(): array
    {
        return [
            'base_url' => self::env('APP_BASE_URL', 'http://127.0.0.1:8000'),
            'token_secret' => self::env('APP_TOKEN_SECRET', 'change-this-secret-key'),
        ];
    }

    private static function env(string $name, string $default): string
    {
        $value = getenv($name);

        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }
}
