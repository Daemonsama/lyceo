<?php

namespace App\Command;

use App\Entity\User;
use App\Service\MailerConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsCommand(
    name: 'app:mailer:test-reset',
    description: 'Envoie un email de réinitialisation de mot de passe (même contenu que le site)',
)]
class TestResetPasswordEmailCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
        private MailerConfig $mailerConfig,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email du compte enregistré en base');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $io->error('Aucun compte avec cet email. Comptes existants : admin@gmail.com, user@gmail.com, farhanesabiyo@gmail.com');

            return Command::FAILURE;
        }

        $resetToken = $this->resetPasswordHelper->generateResetToken($user);

        $emailMessage = (new TemplatedEmail())
            ->from(new Address($this->mailerConfig->getFromEmail(), $this->mailerConfig->getFromName()))
            ->to($email)
            ->subject('Réinitialisation de votre mot de passe — Lyceo Campus')
            ->locale('fr')
            ->htmlTemplate('reset_password/email.html.twig')
            ->textTemplate('reset_password/email.txt.twig')
            ->context(['resetToken' => $resetToken]);

        $logoPath = dirname(__DIR__, 2).'/public/image/logo2-transparent.png';
        if (is_readable($logoPath)) {
            $emailMessage->embedFromPath($logoPath, 'lyceo_logo', 'image/png');
        }

        $this->mailer->send($emailMessage);

        $io->success('Email de réinitialisation envoyé à '.$email);

        return Command::SUCCESS;
    }
}
