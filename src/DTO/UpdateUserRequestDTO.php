<?php

declare(strict_types=1);

namespace Tito\CrudUsers\DTO;

use Tito\CrudUsers\Exception\ValidationException;

final class UpdateUserRequestDTO
{
    private const ALLOWED_GENDERS = ['male', 'female', 'other'];

    private function __construct(
        private string $id,
        private string $email,
        private ?string $password,
        private int $roleId,
        private string $firstName,
        private string $lastName,
        private int $age,
        private string $address,
        private string $phoneNumber,
        private string $gender,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(string $id, array $payload): self
    {
        $email = trim((string) ($payload['email'] ?? ''));
        $password = isset($payload['password']) ? (string) $payload['password'] : null;
        $roleId = (int) ($payload['role_id'] ?? 0);
        $firstName = trim((string) ($payload['first_name'] ?? ''));
        $lastName = trim((string) ($payload['last_name'] ?? ''));
        $age = (int) ($payload['age'] ?? -1);
        $address = trim((string) ($payload['address'] ?? ''));
        $phoneNumber = trim((string) ($payload['phone_number'] ?? ''));
        $gender = strtolower(trim((string) ($payload['gender'] ?? '')));

        $errors = self::validate(
            $id,
            $email,
            $password,
            $roleId,
            $firstName,
            $lastName,
            $age,
            $address,
            $phoneNumber,
            $gender,
        );

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self(
            $id,
            $email,
            $password,
            $roleId,
            $firstName,
            $lastName,
            $age,
            $address,
            $phoneNumber,
            $gender,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    /** @return array<string, string> */
    private static function validate(
        string $id,
        string $email,
        ?string $password,
        int $roleId,
        string $firstName,
        string $lastName,
        int $age,
        string $address,
        string $phoneNumber,
        string $gender,
    ): array {
        $errors = [];

        if ($id === '') {
            $errors['id'] = 'El id es obligatorio.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalido.';
        }

        if ($password !== null && $password !== '' && mb_strlen($password) < 8) {
            $errors['password'] = 'La contrasena debe tener al menos 8 caracteres.';
        }

        if ($roleId <= 0) {
            $errors['role_id'] = 'El role_id es obligatorio y debe ser mayor a 0.';
        }

        if ($firstName === '' || mb_strlen($firstName) > 50) {
            $errors['first_name'] = 'El first_name es obligatorio y maximo de 50 caracteres.';
        }

        if ($lastName === '' || mb_strlen($lastName) > 50) {
            $errors['last_name'] = 'El last_name es obligatorio y maximo de 50 caracteres.';
        }

        if ($age < 0 || $age > 100) {
            $errors['age'] = 'La edad debe estar entre 0 y 100.';
        }

        if ($address === '' || mb_strlen($address) > 255) {
            $errors['address'] = 'La direccion es obligatoria y maximo de 255 caracteres.';
        }

        if (!preg_match('/^\+?[0-9]{7,15}$/', $phoneNumber)) {
            $errors['phone_number'] = 'Telefono invalido. Debe contener solo numeros (7 a 15).';
        }

        if (!in_array($gender, self::ALLOWED_GENDERS, true)) {
            $errors['gender'] = 'Genero invalido. Valores permitidos: male, female, other.';
        }

        return $errors;
    }
}
