<?php

namespace App\Repository;

use App\Entity\HomePromoBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomePromoBlock>
 */
class HomePromoBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomePromoBlock::class);
    }

    public function getSingleton(): HomePromoBlock
    {
        $row = $this->createQueryBuilder('h')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($row instanceof HomePromoBlock) {
            return $row;
        }

        $block = new HomePromoBlock();
        $em = $this->getEntityManager();
        $em->persist($block);
        $em->flush();

        return $block;
    }
}
