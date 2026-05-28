<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\QuizFinalTentative;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizFinalTentative>
 */
class QuizFinalTentativeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizFinalTentative::class);
    }

    public function countByUserAndFormation(User $user, Formation $formation): int
    {
        return (int) $this->count(['user' => $user, 'formation' => $formation]);
    }

    public function save(QuizFinalTentative $tentative, bool $flush = true): void
    {
        $this->getEntityManager()->persist($tentative);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
