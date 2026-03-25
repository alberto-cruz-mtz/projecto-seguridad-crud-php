<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Exception;

use InvalidArgumentException;

final class ValidationException extends InvalidArgumentException
{
    /** @var array<string, string> */
    private array $errors;

    /** @param array<string, string> $errors */
    public function __construct(array $errors, string $message = 'Validation failed.')
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
