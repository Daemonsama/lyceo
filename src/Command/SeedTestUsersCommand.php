<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-test-users',
    description: 'Crée les comptes de test (admin + utilisateur simple) pour le développement local',
)]
final class SeedTestUsersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Met à jour les comptes s\'ils existent déjà (email identique)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        $definitions = [
            [
                'email' => 'admin@gmail.com',
                'plainPassword' => 'admin123',
                'nom' => 'Admin',
                'prenom' => 'Admin',
                'roles' => ['ROLE_SUPER_ADMIN'],
            ],
            [
                'email' => 'user@gmail.com',
                'plainPassword' => 'user123',
                'nom' => 'Utilisateur',
                'prenom' => 'Test',
                'roles' => [],
            ],
        ];

        foreach ($definitions as $def) {
            $user = $this->userRepository->findOneBy(['email' => $def['email']]);

            if ($user !== null && !$force) {
                $io->warning(sprintf('Compte déjà présent : %s (utilisez --force pour le mettre à jour)', $def['email']));
                continue;
            }

            if ($user === null) {
                $user = new User();
                $user->setEmail($def['email']);
                $this->entityManager->persist($user);
            }

            $user->setNom($def['nom']);
            $user->setPrenom($def['prenom']);
            $user->setRoles($def['roles']);
            $user->setIsVerified(true);
            $user->setPassword($this->passwordHasher->hashPassword($user, $def['plainPassword']));
        }

        $this->entityManager->flush();

        $io->success('Comptes de test prêts.');
        $io->listing([
            'Super admin : admin@gmail.com / admin123 (ROLE_SUPER_ADMIN)',
            'Utilisateur : user@gmail.com / user123 (ROLE_USER)',
        ]);

        return Command::SUCCESS;
    }
}
