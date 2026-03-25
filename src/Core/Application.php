<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Core;

use PDO;
use Tito\CrudUsers\Repository\PdoUserRepository;
use Tito\CrudUsers\Repository\UserRepositoryInterface;
use Tito\CrudUsers\Service\AuthenticationService;
use Tito\CrudUsers\Service\Mail\MailerInterface;
use Tito\CrudUsers\Service\Mail\NullMailer;
use Tito\CrudUsers\Service\Mail\PhpMailerService;
use Tito\CrudUsers\Service\Session\SessionManager;
use Tito\CrudUsers\Service\UserService;

final class Application
{
    private static ?PDO $connection = null;
    private static ?UserRepositoryInterface $userRepository = null;
    private static ?MailerInterface $mailer = null;
    private static ?SessionManager $sessionManager = null;
    private static ?UserService $userService = null;
    private static ?AuthenticationService $authenticationService = null;

    public static function userService(): UserService
    {
        if (self::$userService !== null) {
            return self::$userService;
        }

        self::$userService = new UserService(
            self::userRepository(),
            self::mailer(),
            self::connection(),
        );

        return self::$userService;
    }

    public static function authenticationService(): AuthenticationService
    {
        if (self::$authenticationService !== null) {
            return self::$authenticationService;
        }

        self::$authenticationService = new AuthenticationService(
            self::userRepository(),
            self::sessionManager(),
            self::mailer(),
            new TokenUtility(Config::app()['token_secret']),
            Config::app()['base_url'],
        );

        return self::$authenticationService;
    }

    private static function userRepository(): UserRepositoryInterface
    {
        if (self::$userRepository !== null) {
            return self::$userRepository;
        }

        self::$userRepository = new PdoUserRepository(self::connection());

        return self::$userRepository;
    }

    private static function mailer(): MailerInterface
    {
        if (self::$mailer !== null) {
            return self::$mailer;
        }

        $mailConfig = Config::mail();
        if ($mailConfig['host'] === '' || $mailConfig['username'] === '') {
            self::$mailer = new NullMailer();
            return self::$mailer;
        }

        self::$mailer = new PhpMailerService($mailConfig);

        return self::$mailer;
    }

    private static function sessionManager(): SessionManager
    {
        if (self::$sessionManager !== null) {
            return self::$sessionManager;
        }

        self::$sessionManager = new SessionManager();

        return self::$sessionManager;
    }

    private static function connection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        self::$connection = Database::getInstance(Config::database())->getConnection();

        return self::$connection;
    }
}
