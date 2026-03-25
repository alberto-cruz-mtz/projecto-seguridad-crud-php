<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Repository;

use PDO;
use Tito\CrudUsers\Entity\Person;
use Tito\CrudUsers\Entity\Role;
use Tito\CrudUsers\Entity\User;

final class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $connection)
    {
    }

    public function save(User $user): User
    {
        if ($this->existsById($user->getId())) {
            $this->updateUser($user);
            $this->upsertPerson($user);

            return $this->findById($user->getId()) ?? $user;
        }

        $this->insertUser($user);
        $this->insertPerson($user);

        return $this->findById($user->getId()) ?? $user;
    }

    /** @return User[] */
    public function findAll(): array
    {
        $statement = $this->connection->query($this->baseSelectQuery() . ' ORDER BY u.email ASC');
        $rows = $statement->fetchAll();

        return array_map(fn(array $row): User => $this->mapHydratedUser($row), $rows);
    }

    public function findById(string $id): ?User
    {
        $statement = $this->connection->prepare($this->baseSelectQuery() . ' WHERE u.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->mapHydratedUser($row);
    }

    public function findByEmail(string $email): ?User
    {
        $statement = $this->connection->prepare($this->baseSelectQuery() . ' WHERE u.email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->mapHydratedUser($row);
    }

    public function findByUsername(string $username): ?User
    {
        $statement = $this->connection->prepare(
            'SELECT u.id, u.email, u.password, u.role_id, '
            . 'r.id AS role_table_id, r.name AS role_name, r.description AS role_description '
            . 'FROM users u '
            . 'INNER JOIN roles r ON r.id = u.role_id '
            . 'WHERE u.email = :username '
            . 'LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        $role = new Role(
            (int) $row['role_table_id'],
            (string) $row['role_name'],
            isset($row['role_description']) ? (string) $row['role_description'] : null,
        );

        return new User(
            (string) $row['id'],
            (string) $row['email'],
            (string) $row['password'],
            (int) $row['role_id'],
            $role,
        );
    }

    public function delete(User $user): bool
    {
        return $this->deleteById($user->getId());
    }

    public function deleteById(string $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    public function updatePasswordById(string $id, string $passwordHash): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE users SET password = :password WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'password' => $passwordHash,
        ]);

        return $statement->rowCount() > 0;
    }

    private function existsById(string $id): bool
    {
        $statement = $this->connection->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetchColumn() !== false;
    }

    private function insertUser(User $user): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (id, email, password, role_id) VALUES (:id, :email, :password, :role_id)'
        );

        $statement->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'role_id' => $user->getRoleId(),
        ]);
    }

    private function updateUser(User $user): void
    {
        $statement = $this->connection->prepare(
            'UPDATE users SET email = :email, password = :password, role_id = :role_id WHERE id = :id'
        );

        $statement->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'role_id' => $user->getRoleId(),
        ]);
    }

    private function insertPerson(User $user): void
    {
        $person = $user->getPerson();
        if ($person === null) {
            return;
        }

        $statement = $this->connection->prepare(
            'INSERT INTO people (user_id, first_name, last_name, age, address, phone_number, gender) '
            . 'VALUES (:user_id, :first_name, :last_name, :age, :address, :phone_number, :gender)'
        );

        $statement->execute([
            'user_id' => $user->getId(),
            'first_name' => $person->getFirstName(),
            'last_name' => $person->getLastName(),
            'age' => $person->getAge(),
            'address' => $person->getAddress(),
            'phone_number' => $person->getPhoneNumber(),
            'gender' => $person->getGender(),
        ]);
    }

    private function upsertPerson(User $user): void
    {
        $person = $user->getPerson();
        if ($person === null) {
            return;
        }

        $statement = $this->connection->prepare(
            'INSERT INTO people (user_id, first_name, last_name, age, address, phone_number, gender) '
            . 'VALUES (:user_id, :first_name, :last_name, :age, :address, :phone_number, :gender) '
            . 'ON CONFLICT (user_id) DO UPDATE SET '
            . 'first_name = EXCLUDED.first_name, '
            . 'last_name = EXCLUDED.last_name, '
            . 'age = EXCLUDED.age, '
            . 'address = EXCLUDED.address, '
            . 'phone_number = EXCLUDED.phone_number, '
            . 'gender = EXCLUDED.gender'
        );

        $statement->execute([
            'user_id' => $user->getId(),
            'first_name' => $person->getFirstName(),
            'last_name' => $person->getLastName(),
            'age' => $person->getAge(),
            'address' => $person->getAddress(),
            'phone_number' => $person->getPhoneNumber(),
            'gender' => $person->getGender(),
        ]);
    }

    private function baseSelectQuery(): string
    {
        return 'SELECT '
            . 'u.id, u.email, u.password, u.role_id, '
            . 'r.id AS role_table_id, r.name AS role_name, r.description AS role_description, '
            . 'p.user_id AS person_user_id, p.first_name, p.last_name, p.age, p.address, p.phone_number, p.gender '
            . 'FROM users u '
            . 'INNER JOIN roles r ON r.id = u.role_id '
            . 'LEFT JOIN people p ON p.user_id = u.id';
    }

    /** @param array<string, mixed> $row */
    private function mapHydratedUser(array $row): User
    {
        $role = new Role(
            (int) $row['role_table_id'],
            (string) $row['role_name'],
            isset($row['role_description']) ? (string) $row['role_description'] : null,
        );

        $person = null;
        if ($row['person_user_id'] !== null) {
            $person = new Person(
                (string) $row['person_user_id'],
                (string) $row['first_name'],
                (string) $row['last_name'],
                (int) $row['age'],
                (string) $row['address'],
                (string) $row['phone_number'],
                (string) $row['gender'],
            );
        }

        return new User(
            (string) $row['id'],
            (string) $row['email'],
            (string) $row['password'],
            (int) $row['role_id'],
            $role,
            $person,
        );
    }
}
