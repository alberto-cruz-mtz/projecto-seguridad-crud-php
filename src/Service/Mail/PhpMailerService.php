<?php

declare(strict_types=1);

namespace Tito\CrudUsers\Service\Mail;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class PhpMailerService implements MailerInterface
{
    /** @param array<string, mixed> $config */
    public function __construct(private array $config)
    {
    }

    public function send(string $to, string $subject, string $body): void
    {
        $mailer = new PHPMailer(true);

        try {
            $this->configureTransport($mailer);

            $mailer->setFrom((string) $this->config['from_address'], (string) $this->config['from_name']);
            $mailer->addAddress($to);
            $mailer->isHTML(false);
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->send();
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf('No se pudo enviar el correo a %s: %s', $to, $e->getMessage()),
                (int) $e->getCode(),
                $e,
            );
        }
    }

    private function configureTransport(PHPMailer $mailer): void
    {
        $host = (string) ($this->config['host'] ?? '');
        $username = (string) ($this->config['username'] ?? '');

        if ($host === '' || $username === '') {
            throw new RuntimeException('Configuracion SMTP incompleta: MAIL_HOST y MAIL_USERNAME son obligatorios.');
        }

        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Port = (int) ($this->config['port'] ?? 587);
        $mailer->SMTPAuth = true;
        $mailer->Username = $username;
        $mailer->Password = (string) ($this->config['password'] ?? '');

        $encryption = strtolower((string) ($this->config['encryption'] ?? 'tls'));
        if ($encryption === 'ssl') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            return;
        }

        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
}
