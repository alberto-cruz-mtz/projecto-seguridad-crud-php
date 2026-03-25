<?php

declare(strict_types=1);

namespace Tito\CrudUsers\DTO;

use Tito\CrudUsers\Exception\ValidationException;

final class LoginRequestDTO
{
    private function __construct(
        private string $username,
        private string $password,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $username = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        $errors = self::validate($username, $password);
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self($username, $password);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /** @return array<string, string> */
    private static function validate(string $username, string $password): array
    {
        $errors = [];

        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $errors['username'] = 'El username debe ser un correo electronico valido.';
        }

        if ($password === '') {
            $errors['password'] = 'La contrasena es obligatoria.';
        }

        return $errors;
    }
}
