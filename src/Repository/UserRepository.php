<?php

namespace Tito\App\Repository;

use Tito\App\Core\UUID;
use Tito\App\Entity\User;

interface UserRepository
{
    public function findByUsername(string $username): ?User;

}