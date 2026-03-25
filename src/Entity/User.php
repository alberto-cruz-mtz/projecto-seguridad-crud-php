<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Entity;

final class User
{
    public function __construct(
        private string $id,
        private string $email,
        private string $password,
        private int $roleId,
        private ?Role $role = null,
        private ?Person $person = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): void
    {
        $this->person = $person;
    }

    /** @return array<string, mixed> */
    public function toArray(bool $includePassword = false): array
    {
        $payload = [
            'id' => $this->id,
            'email' => $this->email,
            'role_id' => $this->roleId,
            'role' => $this->role?->toArray(),
            'person' => $this->person?->toArray(),
        ];

        if ($includePassword) {
            $payload['password'] = $this->password;
        }

        return $payload;
    }
}
