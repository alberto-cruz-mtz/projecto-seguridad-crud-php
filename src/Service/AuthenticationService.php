<?php

namespace Tito\App\Service;

use RuntimeException;
use Tito\App\Repository\UserRepository;

readonly class AuthenticationService
{
    public function __construct(
        private UserRepository $userRepository,
        private SessionServiceInterface $sessionService
    ) {
    }

    public function authenticate(string $email, string $password): array
    {
        $user = $this->userRepository->findByUsername($email);

        if ($user === null) {
            return ['error' => 'Credenciales inválidas.'];
        }

        $passwordIsValid = password_verify($password, $user->getPasswordHash());

        if (!$passwordIsValid) {
            return ['error' => 'Credenciales inválidas.'];
        }

        $authenticatedUserData = [
            'id'          => (string) $user->getId(),
            'displayName' => $user->getFullName(),
            'email'       => $user->getEmail(),
            'role'        => $user->getRole()->getName()->value,
        ];

        try {
            $this->sessionService->startAuthenticatedSession($authenticatedUserData);
        } catch (RuntimeException $sessionException) {
            return ['error' => 'No se pudo iniciar la sesión. Intenta de nuevo.'];
        }

        return $authenticatedUserData;
    }

    public function logout(): void
    {
        $this->sessionService->destroySession();
    }
}