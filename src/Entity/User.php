<?php

namespace Tito\App\Entity;

use Tito\App\Core\UUID;

class User
{
    private UUID $id;
    private string $fullName;
    private string $email;
    private string $passwordHash;

    private Role $role;

    public function __construct(UUID $id, string $fullName, string $email, string $passwordHash, Role $role)
    {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
    }

    public function getId(): UUID
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}
