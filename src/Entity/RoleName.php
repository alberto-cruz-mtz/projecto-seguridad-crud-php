<?php

namespace Tito\App\Entity;

use InvalidArgumentException;

enum RoleName: string
{
    case ADMIN = 'admin';
    case STANDARD_USER = 'standard_user';

    static function fromString(string $roleName): RoleName
    {
        return match ($roleName) {
            'admin' => self::ADMIN,
            'standard_user' => self::STANDARD_USER,
            default => throw new InvalidArgumentException("Invalid role name: $roleName"),
        };
    }
}
