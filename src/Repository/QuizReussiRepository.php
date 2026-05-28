<?php

namespace App\Repository;

use App\Entity\Quiz;
use App\Entity\QuizReussi;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizReussi>
 */
class QuizReussiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizReussi::class);
    }

    public function hasPassed(User $user, Quiz $quiz): bool
    {
        return $this->count(['user' => $user, 'quiz' => $quiz]) > 0;
    }

    public function save(QuizReussi $quizReussi, bool $flush = true): void
    {
        $this->getEntityManager()->persist($quizReussi);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
