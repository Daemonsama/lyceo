<?php

namespace App\Service;

use App\Repository\CategorieRepository;
use App\Repository\ChapitreRepository;
use App\Repository\FormationPromoCodeRepository;
use App\Repository\FormationRepository;
use App\Repository\FormationUserRepository;
use App\Repository\UserRepository;

final class AdminPlatformOverviewService
{
    public function __construct(
        private readonly FormationRepository $formationRepository,
        private readonly CategorieRepository $categorieRepository,
        private readonly UserRepository $userRepository,
        private readonly ChapitreRepository $chapitreRepository,
        private readonly FormationUserRepository $formationUserRepository,
        private readonly FormationPromoCodeRepository $promoCodeRepository,
    ) {
    }

    /**
     * @return array{
     *     formations: int,
     *     categories: int,
     *     users: int,
     *     chapitres: int,
     *     sales: int,
     *     revenue: float,
     *     promoCodes: int
     * }
     */
    public function getOverview(): array
    {
        $sales = $this->formationUserRepository->getStatistics();

        return [
            'formations' => $this->formationRepository->count([]),
            'categories' => $this->categorieRepository->count([]),
            'users' => $this->userRepository->count([]),
            'chapitres' => $this->chapitreRepository->count([]),
            'sales' => $sales['totalCount'],
            'revenue' => $sales['totalRevenue'],
            'promoCodes' => $this->promoCodeRepository->count(['active' => true]),
        ];
    }

    /**
     * @return array{
     *     overview: array<string, int|float>,
     *     payments: array<string, int|float>,
     *     topFormations: list<array<string, int|float|string|null>>,
     *     buyers: int,
     *     rates: array<string, int>
     * }
     */
    public function getDashboardData(): array
    {
        $overview = $this->getOverview();
        $payments = $this->formationUserRepository->getPaymentOverview();
        $buyers = $this->formationUserRepository->countDistinctBuyers();
        $users = (int) $overview['users'];
        $sales = (int) $payments['totalCount'];

        $buyerRate = $users > 0 ? (int) round($buyers / $users * 100) : 0;
        $stripeRate = $sales > 0 ? (int) round($payments['stripeCount'] / $sales * 100) : 0;
        $monthSalesRate = $sales > 0 ? (int) round($payments['monthCount'] / $sales * 100) : 0;

        return [
            'overview' => $overview,
            'payments' => $payments,
            'topFormations' => $this->formationUserRepository->getTopFormations(5),
            'buyers' => $buyers,
            'rates' => [
                'buyers' => $buyerRate,
                'nonBuyers' => max(0, 100 - $buyerRate),
                'stripe' => $stripeRate,
                'monthSales' => $monthSalesRate,
            ],
        ];
    }
}
