<?php

namespace App\Command;

use App\Repository\FormationPromoCodeRepository;
use App\Service\StripePromoCodeSyncService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:stripe:sync-promo-codes',
    description: 'Synchronise les codes promo admin avec Stripe',
)]
final class SyncPromoCodesCommand extends Command
{
    public function __construct(
        private readonly FormationPromoCodeRepository $promoCodeRepository,
        private readonly StripePromoCodeSyncService $promoCodeSync,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $promoCodes = $this->promoCodeRepository->findBy(['active' => true]);
        $synced = 0;
        $errors = [];

        foreach ($promoCodes as $promoCode) {
            if ($promoCode->getStripePromotionCodeId() !== null) {
                continue;
            }

            try {
                $this->promoCodeSync->syncPromoCode($promoCode);
                ++$synced;
                $io->writeln(sprintf('OK : %s (formation #%d)', $promoCode->getCode(), $promoCode->getFormation()?->getId()));
            } catch (\Throwable $e) {
                $errors[] = sprintf('%s : %s', $promoCode->getCode(), $e->getMessage());
            }
        }

        if ($synced > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d code(s) synchronisé(s) avec Stripe.', $synced));
        }

        if ($errors !== []) {
            $io->error('Erreurs de synchronisation :');
            $io->listing($errors);

            return Command::FAILURE;
        }

        if ($synced === 0) {
            $io->info('Aucun code en attente de synchronisation.');
        }

        return Command::SUCCESS;
    }
}
