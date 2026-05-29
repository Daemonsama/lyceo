<?php

namespace App\Service;

use App\Entity\ContactMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class ContactNotifier
{
    public function __construct(
        private MailerInterface $mailer,
        private string $contactEmail,
        private string $mailerFromEmail,
        private string $mailerFromName,
    ) {
    }

    public function notify(ContactMessage $contact): void
    {
        if ($this->contactEmail === '') {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->replyTo(new Address((string) $contact->getEmail(), (string) $contact->getNom()))
            ->to($this->contactEmail)
            ->subject('Nouveau message de contact — '.$contact->getNom())
            ->htmlTemplate('emails/contact_notification.html.twig')
            ->context(['contact' => $contact]);

        $this->mailer->send($email);
    }
}
