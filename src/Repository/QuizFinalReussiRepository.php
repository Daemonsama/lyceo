<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\QuizFinalReussi;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizFinalReussi>
 */
class QuizFinalReussiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizFinalReussi::class);
    }

    public function hasPassed(User $user, Formation $formation): bool
    {
        return $this->count(['user' => $user, 'formation' => $formation]) > 0;
    }

    public function save(QuizFinalReussi $quizFinalReussi, bool $flush = true): void
    {
        $this->getEntityManager()->persist($quizFinalReussi);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
