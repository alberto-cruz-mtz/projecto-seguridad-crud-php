<?php

namespace Tito\App\Repository;

use PDO;
use Tito\App\Core\Database;
use Tito\App\Core\UUID;
use Tito\App\Entity\Role;
use Tito\App\Entity\RoleName;
use Tito\App\Entity\User;

readonly class PostgresUserRepository implements UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername(string $username): ?User
    {
        $query = <<<SQL
            SELECT u.id, u.email, u.password, u.full_name, r.name as role_name FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.email = ?
        SQL;

        $statement = $this->db->prepare($query);
        $statement->execute([$username]);
        $user = $statement->fetch();

        if (!$user) return null;

        return new User(
            new UUID($user['id']),
            $user['full_name'],
            $user['email'],
            $user['password'],
            new Role(0, RoleName::fromString($user['role_name']), '')
        );
    }
}