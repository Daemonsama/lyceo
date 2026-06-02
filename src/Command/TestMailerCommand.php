<?php

namespace App\Command;

use App\Service\MailerConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:mailer:test',
    description: 'Teste l\'envoi SMTP AlwaysData (réinitialisation mot de passe)',
)]
class TestMailerCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private MailerConfig $mailerConfig,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('to', InputArgument::REQUIRED, 'Adresse email de test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->mailerConfig->isConfigured()) {
            $io->error('Renseignez MAILER_USER et MAILER_PASSWORD dans .env.local (voir .env.local.example).');

            return Command::FAILURE;
        }

        $to = (string) $input->getArgument('to');

        try {
            $this->mailer->send(
                (new Email())
                    ->from($this->mailerConfig->getFromEmail())
                    ->to($to)
                    ->subject('Test SMTP SPC Formation')
                    ->html('<p>Si vous recevez cet email, la configuration AlwaysData fonctionne.</p>')
            );
            $io->success('Email envoyé à '.$to.' depuis '.$this->mailerConfig->getFromEmail());

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
