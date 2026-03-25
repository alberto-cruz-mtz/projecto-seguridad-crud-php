<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Service\Mail;

final class NullMailer implements MailerInterface
{
    public function send(string $to, string $subject, string $body): void
    {
    }
}
