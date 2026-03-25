<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Repository;

use Tito\CrudUsers\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): User;

    /** @return User[] */
    public function findAll(): array;

    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    public function delete(User $user): bool;

    public function deleteById(string $id): bool;

    public function updatePasswordById(string $id, string $passwordHash): bool;
}
