<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\FormationUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Formation>
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function save(Formation $formation): void
    {
        $em = $this->getEntityManager();
        $em->persist($formation);
        $em->flush();
    }

    public function delete(Formation $formation, bool $flush = true): void
    {
        $em = $this->getEntityManager();

        foreach ($em->getRepository(FormationUser::class)->findBy(['formation' => $formation]) as $formationUser) {
            $em->remove($formationUser);
        }

        foreach ($formation->getChapitres()->toArray() as $chapitre) {
            $em->remove($chapitre);
        }

        $em->remove($formation);

        if ($flush) {
            $em->flush();
        }
    }
}
