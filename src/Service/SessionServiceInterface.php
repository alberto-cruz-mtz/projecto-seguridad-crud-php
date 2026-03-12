<?php

namespace Tito\App\Service;

interface SessionServiceInterface
{
    public function startAuthenticatedSession(array $authenticatedUser): void;

    public function destroySession(): void;

    public function hasActiveSession(): bool;

    public function getAuthenticatedUser(): ?array;
}
