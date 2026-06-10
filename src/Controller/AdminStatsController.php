<?php

namespace App\Controller;

use App\Repository\FormationUserRepository;
use App\Service\AdminPlatformOverviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminStatsController extends AbstractController
{
    #[Route('/statistiques', name: 'app_admin_stats', methods: ['GET'])]
    public function stats(
        FormationUserRepository $formationUserRepository,
        AdminPlatformOverviewService $platformOverview,
    ): Response {
        return $this->render('admin_stats/index.html.twig', [
            'dashboard' => $platformOverview->getDashboardData(),
            'evolution' => $formationUserRepository->getSalesEvolution(),
        ]);
    }
}
