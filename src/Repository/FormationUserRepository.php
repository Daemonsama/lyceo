<?php

namespace App\Repository;

use App\Entity\FormationUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationUser>
 */
class FormationUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationUser::class);
    }

    public function save(FormationUser $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<FormationUser>
     */
    public function findAllForAdmin(): array
    {
        return $this->createQueryBuilder('fu')
            ->addSelect('u', 'f')
            ->innerJoin('fu.user', 'u')
            ->innerJoin('fu.formation', 'f')
            ->orderBy('fu.date_achat', 'DESC')
            ->addOrderBy('fu.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{totalCount: int, totalRevenue: float}
     */
    public function getStatistics(): array
    {
        $totalCount = (int) $this->createQueryBuilder('fu')
            ->select('COUNT(fu.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalRevenueCents = (int) $this->createQueryBuilder('fu')
            ->select('COALESCE(SUM(fu.montant), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalCount' => $totalCount,
            'totalRevenue' => $totalRevenueCents / 100,
        ];
    }

    /**
     * @return array{
     *     totalCount: int,
     *     totalRevenue: float,
     *     monthCount: int,
     *     monthRevenue: float,
     *     stripeCount: int
     * }
     */
    public function getPaymentOverview(): array
    {
        $stats = $this->getStatistics();
        $monthStart = new \DateTime('first day of this month midnight');

        $monthCount = (int) $this->createQueryBuilder('fu')
            ->select('COUNT(fu.id)')
            ->andWhere('fu.date_achat >= :monthStart')
            ->setParameter('monthStart', $monthStart)
            ->getQuery()
            ->getSingleScalarResult();

        $monthRevenueCents = (int) $this->createQueryBuilder('fu')
            ->select('COALESCE(SUM(fu.montant), 0)')
            ->andWhere('fu.date_achat >= :monthStart')
            ->setParameter('monthStart', $monthStart)
            ->getQuery()
            ->getSingleScalarResult();

        $stripeCount = (int) $this->createQueryBuilder('fu')
            ->select('COUNT(fu.id)')
            ->andWhere('fu.mode_paiement = :mode')
            ->setParameter('mode', 'stripe')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            ...$stats,
            'monthCount' => $monthCount,
            'monthRevenue' => $monthRevenueCents / 100,
            'stripeCount' => $stripeCount,
        ];
    }

    public function countDistinctBuyers(): int
    {
        return (int) $this->createQueryBuilder('fu')
            ->select('COUNT(DISTINCT IDENTITY(fu.user))')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<array{
     *     id: int,
     *     titre: string,
     *     apercuFilename: string|null,
     *     prix: float,
     *     sales: int,
     *     revenue: float
     * }>
     */
    public function getTopFormations(int $limit = 5): array
    {
        $rows = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT f.id,
                    f.titre,
                    f.apercu_filename AS apercu_filename,
                    f.prix,
                    COUNT(fu.id) AS sales_count,
                    COALESCE(SUM(fu.montant), 0) AS revenue_cents
             FROM formation f
             INNER JOIN formation_user fu ON fu.formation_id = f.id
             GROUP BY f.id, f.titre, f.apercu_filename, f.prix
             HAVING sales_count > 0
             ORDER BY sales_count DESC
             LIMIT '.max(1, $limit),
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'titre' => (string) $row['titre'],
            'apercuFilename' => $row['apercu_filename'] !== null ? (string) $row['apercu_filename'] : null,
            'prix' => ((int) $row['prix']) / 100,
            'sales' => (int) $row['sales_count'],
            'revenue' => ((int) $row['revenue_cents']) / 100,
        ], $rows);
    }

    /**
     * Évolution mensuelle sur les N derniers mois (labels, ventes, revenus en €).
     *
     * @return array{labels: list<string>, sales: list<int>, revenue: list<float>}
     */
    public function getSalesEvolution(int $months = 12): array
    {
        $labels = [];
        $salesByMonth = [];
        $revenueByMonth = [];

        $cursor = new \DateTime('first day of this month midnight');
        $cursor->modify(sprintf('-%d months', $months - 1));

        for ($i = 0; $i < $months; ++$i) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('m/Y');
            $salesByMonth[$key] = 0;
            $revenueByMonth[$key] = 0.0;
            $cursor->modify('+1 month');
        }

        $start = new \DateTime('first day of this month midnight');
        $start->modify(sprintf('-%d months', $months - 1));

        $rows = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT DATE_FORMAT(date_achat, \'%Y-%m\') AS month_key,
                    COUNT(*) AS sales,
                    COALESCE(SUM(montant), 0) AS revenue_cents
             FROM formation_user
             WHERE date_achat >= :start
             GROUP BY month_key
             ORDER BY month_key ASC',
            ['start' => $start->format('Y-m-d')],
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $key = $row['month_key'];
            if (!isset($salesByMonth[$key])) {
                continue;
            }
            $salesByMonth[$key] = (int) $row['sales'];
            $revenueByMonth[$key] = ((int) $row['revenue_cents']) / 100;
        }

        return [
            'labels' => $labels,
            'sales' => array_values($salesByMonth),
            'revenue' => array_values($revenueByMonth),
        ];
    }
}
