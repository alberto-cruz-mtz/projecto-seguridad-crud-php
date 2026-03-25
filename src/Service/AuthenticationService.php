<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Service;

use Tito\CrudUsers\DTO\LoginRequestDTO;
use Tito\CrudUsers\DTO\PasswordResetConfirmDTO;
use Tito\CrudUsers\DTO\PasswordResetRequestDTO;
use Tito\CrudUsers\Entity\User;
use Tito\CrudUsers\Exception\NotFoundException;
use Tito\CrudUsers\Exception\AuthenticationException;
use Tito\CrudUsers\Repository\UserRepositoryInterface;
use Tito\CrudUsers\Service\Mail\MailerInterface;
use Tito\CrudUsers\Service\Session\SessionManager;
use Tito\CrudUsers\Core\TokenUtility;

final class AuthenticationService
{
    private const RESET_PASSWORD_COOKIE = 'reset_password_token';
    private const RESET_TOKEN_EXPIRATION_SECONDS = 1800;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SessionManager $sessionManager,
        private MailerInterface $mailer,
        private TokenUtility $tokenUtility,
        private string $appBaseUrl,
    ) {
    }

    /** @return array<string, mixed> */
    public function login(LoginRequestDTO $dto): array
    {
        $user = $this->userRepository->findByUsername($dto->getUsername());
        if ($user === null) {
            throw new AuthenticationException('Credenciales invalidas.');
        }

        if (!password_verify($dto->getPassword(), $user->getPassword())) {
            throw new AuthenticationException('Credenciales invalidas.');
        }

        $this->sessionManager->start();
        $this->sessionManager->regenerateId();
        $this->sessionManager->set('auth_user', $this->buildSessionUserPayload($user));

        return $this->buildSessionUserPayload($user);
    }

    /** @return array<string, mixed> */
    public function requestPasswordReset(PasswordResetRequestDTO $dto): array
    {
        $user = $this->userRepository->findByEmail($dto->getEmail());
        if ($user === null) {
            throw new NotFoundException('No existe un usuario registrado con ese correo.');
        }

        $token = $this->tokenUtility->generate(
            [
                'sub' => $user->getId(),
                'email' => $user->getEmail(),
                'purpose' => 'password_reset',
            ],
            self::RESET_TOKEN_EXPIRATION_SECONDS,
        );

        $this->storeResetTokenInCookie($token);
        $this->mailer->send(
            $user->getEmail(),
            'Restablecimiento de contrasena',
            $this->buildPasswordResetEmailBody($user->getEmail(), $token),
        );

        return [
            'message' => 'Se envio un correo con instrucciones para restablecer la contrasena.',
            'expires_in' => self::RESET_TOKEN_EXPIRATION_SECONDS,
        ];
    }

    public function validateResetTokenFromCookie(): bool
    {
        $token = (string) ($_COOKIE[self::RESET_PASSWORD_COOKIE] ?? '');
        if ($token === '') {
            return false;
        }

        $payload = $this->tokenUtility->validateAndDecode($token);
        if ($payload === null) {
            return false;
        }

        return ($payload['purpose'] ?? '') === 'password_reset';
    }

    /** @return array<string, mixed> */
    public function confirmPasswordReset(PasswordResetConfirmDTO $dto): array
    {
        $cookieToken = (string) ($_COOKIE[self::RESET_PASSWORD_COOKIE] ?? '');
        if ($cookieToken === '') {
            throw new AuthenticationException('No existe token de restablecimiento en cookies.');
        }

        if (!hash_equals($cookieToken, $dto->getToken())) {
            throw new AuthenticationException('El token enviado no coincide con el token de la cookie.');
        }

        $payload = $this->tokenUtility->validateAndDecode($cookieToken);
        if ($payload === null || ($payload['purpose'] ?? '') !== 'password_reset') {
            throw new AuthenticationException('Token invalido o expirado.');
        }

        if (($payload['email'] ?? '') !== $dto->getEmail()) {
            throw new AuthenticationException('El correo no coincide con el token.');
        }

        $user = $this->userRepository->findByEmail($dto->getEmail());
        if ($user === null) {
            throw new NotFoundException('No existe un usuario registrado con ese correo.');
        }

        $updated = $this->userRepository->updatePasswordById(
            $user->getId(),
            password_hash($dto->getNewPassword(), PASSWORD_DEFAULT),
        );

        if (!$updated) {
            throw new AuthenticationException('No fue posible actualizar la contrasena.');
        }

        $this->clearResetTokenCookie();

        return ['message' => 'Contrasena restablecida correctamente.'];
    }

    /** @return array<string, mixed> */
    private function buildSessionUserPayload(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role_id' => $user->getRoleId(),
            'role' => $user->getRole()?->toArray(),
        ];
    }

    private function storeResetTokenInCookie(string $token): void
    {
        setcookie(
            self::RESET_PASSWORD_COOKIE,
            $token,
            [
                'expires' => time() + self::RESET_TOKEN_EXPIRATION_SECONDS,
                'path' => '/',
                'domain' => '',
                'secure' => $this->isSecureConnection(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private function clearResetTokenCookie(): void
    {
        setcookie(
            self::RESET_PASSWORD_COOKIE,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => $this->isSecureConnection(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private function buildPasswordResetEmailBody(string $email, string $token): string
    {
        $baseUrl = rtrim($this->appBaseUrl, '/');
        $link = sprintf('%s/password-reset?token=%s&email=%s', $baseUrl, urlencode($token), urlencode($email));

        return sprintf(
            "Hola,\n\nRecibimos una solicitud para restablecer tu contrasena.\n\nAbre el siguiente enlace para continuar:\n%s\n\nEste enlace expira en 30 minutos.",
            $link,
        );
    }

    private function isSecureConnection(): bool
    {
        $https = $_SERVER['HTTPS'] ?? '';
        if ($https !== '' && strtolower((string) $https) !== 'off') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}
