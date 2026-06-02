<?php

namespace App\Service;

final class MailerConfig
{
    public function __construct(
        private string $mailerDsn,
        private string $mailerFrom,
        private string $mailerFromName = 'SPC Formation',
    ) {
    }

    public function isConfigured(): bool
    {
        return !str_starts_with($this->mailerDsn, 'null://');
    }

    public function getFromEmail(): string
    {
        return $this->mailerFrom;
    }

    public function getFromName(): string
    {
        return $this->mailerFromName;
    }
}
