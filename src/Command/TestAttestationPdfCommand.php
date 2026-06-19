<?php

namespace App\Command;

use App\Entity\Formation;
use App\Entity\QuizFinalReussi;
use App\Entity\User;
use App\Repository\FormationRepository;
use App\Repository\QuizFinalReussiRepository;
use App\Repository\UserRepository;
use App\Service\AttestationPdfGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:attestation:test-pdf', description: 'Génère un PDF d\'attestation de test')]
final class TestAttestationPdfCommand extends Command
{
    public function __construct(
        private AttestationPdfGenerator $attestationPdfGenerator,
        private QuizFinalReussiRepository $quizFinalReussiRepository,
        private UserRepository $userRepository,
        private FormationRepository $formationRepository,
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $reussi = $this->quizFinalReussiRepository->findOneBy([], ['dateReussite' => 'DESC']);
        if (!$reussi instanceof QuizFinalReussi) {
            $io->error('Aucun quiz final réussi en base. Validez d\'abord un module.');

            return Command::FAILURE;
        }

        $user = $reussi->getUser();
        $formation = $reussi->getFormation();

        try {
            $response = $this->attestationPdfGenerator->createDownloadResponse($user, $formation, $reussi);
            $path = $this->projectDir.'/var/test-attestation.pdf';
            file_put_contents($path, $response->getContent());
            $io->success('PDF généré : '.$path.' ('.strlen($response->getContent()).' octets)');
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
