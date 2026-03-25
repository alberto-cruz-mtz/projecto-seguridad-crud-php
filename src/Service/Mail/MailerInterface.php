<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Service\Mail;

interface MailerInterface
{
    public function send(string $to, string $subject, string $body): void;
}
