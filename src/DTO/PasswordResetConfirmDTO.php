<?php

declare(strict_types=1);

namespace Tito\CrudUsers\DTO;

use Tito\CrudUsers\Exception\ValidationException;

final class PasswordResetConfirmDTO
{
    private function __construct(
        private string $email,
        private string $token,
        private string $newPassword,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $email = trim((string) ($payload['email'] ?? ''));
        $token = trim((string) ($payload['token'] ?? ''));
        $newPassword = (string) ($payload['new_password'] ?? '');

        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalido.';
        }

        if ($token === '') {
            $errors['token'] = 'El token es obligatorio.';
        }

        if (mb_strlen($newPassword) < 8) {
            $errors['new_password'] = 'La nueva contrasena debe tener al menos 8 caracteres.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self($email, $token, $newPassword);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}
