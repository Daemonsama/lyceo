<?php

namespace App\Repository;

use App\Entity\Quiz;
use App\Entity\QuizTentative;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizTentative>
 */
class QuizTentativeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizTentative::class);
    }

    public function countByUserAndQuiz(User $user, Quiz $quiz): int
    {
        return (int) $this->count(['user' => $user, 'quiz' => $quiz]);
    }

    public function save(QuizTentative $tentative, bool $flush = true): void
    {
        $this->getEntityManager()->persist($tentative);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
