<?php

declare(strict_types=1);

namespace Tito\CrudUsers\DTO;

use Tito\CrudUsers\Exception\ValidationException;

final class PasswordResetRequestDTO
{
    private function __construct(private string $email)
    {
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $email = trim((string) ($payload['email'] ?? ''));

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalido.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self($email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
