<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\FormationPromoCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationPromoCode>
 */
class FormationPromoCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationPromoCode::class);
    }

    public function findActiveByFormationAndCode(Formation $formation, string $code): ?FormationPromoCode
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.formation = :formation')
            ->andWhere('UPPER(p.code) = :code')
            ->andWhere('p.active = true')
            ->setParameter('formation', $formation)
            ->setParameter('code', strtoupper(trim($code)))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
