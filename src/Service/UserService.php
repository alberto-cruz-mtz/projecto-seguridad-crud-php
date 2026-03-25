<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Service;

use PDO;
use RuntimeException;
use Tito\CrudUsers\Core\UUID;
use Tito\CrudUsers\DTO\CreateUserRequestDTO;
use Tito\CrudUsers\DTO\UpdateUserRequestDTO;
use Tito\CrudUsers\Entity\Person;
use Tito\CrudUsers\Entity\User;
use Tito\CrudUsers\Exception\NotFoundException;
use Tito\CrudUsers\Repository\UserRepositoryInterface;
use Tito\CrudUsers\Service\Mail\MailerInterface;

final class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MailerInterface $mailer,
        private PDO $connection,
    ) {
    }

    /** @return User[] */
    public function findAll(): array
    {
        return $this->userRepository->findAll();
    }

    public function findById(string $id): User
    {
        $user = $this->userRepository->findById($id);
        if ($user !== null) {
            return $user;
        }

        throw new NotFoundException(sprintf('Usuario con id %s no encontrado.', $id));
    }

    public function create(CreateUserRequestDTO $dto): User
    {
        if ($this->userRepository->findByEmail($dto->getEmail()) !== null) {
            throw new RuntimeException('El correo electronico ya esta registrado.');
        }

        $user = new User(
            UUID::randomUUID()->toString(),
            $dto->getEmail(),
            password_hash($dto->getPassword(), PASSWORD_DEFAULT),
            $dto->getRoleId(),
        );

        $person = new Person(
            $user->getId(),
            $dto->getFirstName(),
            $dto->getLastName(),
            $dto->getAge(),
            $dto->getAddress(),
            $dto->getPhoneNumber(),
            $dto->getGender(),
        );
        $user->setPerson($person);

        try {
            $this->connection->beginTransaction();
            $saved = $this->userRepository->save($user);
            $this->connection->commit();
        } catch (\Throwable $throwable) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $throwable;
        }

        $this->mailer->send(
            $saved->getEmail(),
            'Credenciales de acceso',
            $this->buildCredentialsEmailBody($saved->getEmail(), $dto->getPassword()),
        );

        return $saved;
    }

    public function update(UpdateUserRequestDTO $dto): User
    {
        $existingUser = $this->userRepository->findById($dto->getId());
        if ($existingUser === null) {
            throw new NotFoundException(sprintf('Usuario con id %s no encontrado.', $dto->getId()));
        }

        $emailOwner = $this->userRepository->findByEmail($dto->getEmail());
        if ($emailOwner !== null && $emailOwner->getId() !== $dto->getId()) {
            throw new RuntimeException('El correo electronico ya esta registrado por otro usuario.');
        }

        $password = $existingUser->getPassword();
        $plainPassword = $dto->getPassword();
        if ($plainPassword !== null && $plainPassword !== '') {
            $password = password_hash($plainPassword, PASSWORD_DEFAULT);
        }

        $updatedUser = new User(
            $dto->getId(),
            $dto->getEmail(),
            $password,
            $dto->getRoleId(),
        );

        $updatedUser->setPerson(
            new Person(
                $dto->getId(),
                $dto->getFirstName(),
                $dto->getLastName(),
                $dto->getAge(),
                $dto->getAddress(),
                $dto->getPhoneNumber(),
                $dto->getGender(),
            )
        );

        return $this->userRepository->save($updatedUser);
    }

    public function deleteById(string $id): void
    {
        $deleted = $this->userRepository->deleteById($id);
        if ($deleted) {
            return;
        }

        throw new NotFoundException(sprintf('Usuario con id %s no encontrado.', $id));
    }

    private function buildCredentialsEmailBody(string $email, string $plainPassword): string
    {
        return sprintf(
            "Hola,\n\nTu cuenta ha sido creada correctamente.\n\nEmail: %s\nContrasena: %s\n\nPor seguridad, cambia tu contrasena al iniciar sesion.",
            $email,
            $plainPassword,
        );
    }
}
