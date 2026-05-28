<?php

namespace App\Repository;

use App\Entity\HomePageContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomePageContent>
 */
class HomePageContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomePageContent::class);
    }

    /**
     * Une seule ligne en base : créée avec les textes par défaut si absente.
     */
    public function getSingleton(): HomePageContent
    {
        $existing = $this->createQueryBuilder('h')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existing instanceof HomePageContent) {
            return $existing;
        }

        $content = new HomePageContent();
        $em = $this->getEntityManager();
        $em->persist($content);
        $em->flush();

        return $content;
    }
}
