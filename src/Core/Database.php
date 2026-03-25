<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Core;

use PDO;
use PDOException;
use RuntimeException;
use InvalidArgumentException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private const REQUIRED_CONFIG_KEYS = ['host', 'port', 'dbname', 'user', 'password'];
    private const DEFAULT_OPTIONS = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ];

    private function __construct(array $config)
    {
        $this->validateConfig($config);

        $dsn     = $this->buildDsn($config);
        $options = $this->mergeOptions($config['options'] ?? []);

        $this->connection = $this->createConnection($dsn, $config['user'], $config['password'], $options);
    }

    private function __clone() {}

    public static function getInstance(array $config = []): static
    {
        if (static::$instance !== null) {
            return static::$instance;
        }

        if (empty($config)) {
            throw new InvalidArgumentException('Database configuration is required on first initialization.');
        }

        static::$instance = new static($config);

        return static::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public static function resetInstance(): void
    {
        static::$instance = null;
    }

    private function validateConfig(array $config): void
    {
        $missingKeys = array_diff(self::REQUIRED_CONFIG_KEYS, array_keys($config));

        if (empty($missingKeys)) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf('Missing required database config keys: %s', implode(', ', $missingKeys))
        );
    }

    private function buildDsn(array $config): string
    {
        return sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $config['host'],
            (int) $config['port'],
            $config['dbname']
        );
    }

    private function mergeOptions(array $customOptions): array
    {
        return $customOptions + self::DEFAULT_OPTIONS;
    }

    private function createConnection(string $dsn, string $user, string $password, array $options): PDO
    {
        try {
            return new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException(
                sprintf('Failed to connect to the database: %s', $e->getMessage()),
                (int) $e->getCode(),
                $e
            );
        }
    }
}
