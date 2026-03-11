<?php

namespace Tito\App\Controller;

class AuthenticationController
{
    public function login(): void
    {
        require_once __DIR__ . "/../View/login.html";
    }
}