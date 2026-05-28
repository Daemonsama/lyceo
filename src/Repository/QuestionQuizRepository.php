<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\QuestionQuiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuestionQuiz>
 */
class QuestionQuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionQuiz::class);
    }

    public function save(QuestionQuiz $question, bool $flush = true): void
    {
        $this->getEntityManager()->persist($question);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(QuestionQuiz $question, bool $flush = true): void
    {
        $this->getEntityManager()->remove($question);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Toutes les questions valides des quiz de chapitres d'une formation.
     *
     * @return QuestionQuiz[]
     */
    public function findValidQuestionsForFormation(Formation $formation): array
    {
        // Sélection des questions "jouables" :
        // - la question doit avoir AU MOINS une réponse correcte
        // - on ne joint pas question.reponses pour éviter tout risque de "filtrer" les mauvaises réponses
        return $this->createQueryBuilder('question')
            ->distinct()
            ->innerJoin('question.quiz', 'quiz')
            ->innerJoin('quiz.chapitre', 'chapitre')
            ->andWhere('chapitre.formation = :formation')
            ->andWhere('EXISTS (
                SELECT 1
                FROM App\\Entity\\ReponseQuiz rq
                WHERE rq.question = question AND rq.estCorrecte = true
            )')
            ->setParameter('formation', $formation)
            ->orderBy('chapitre.ordre', 'ASC')
            ->addOrderBy('question.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $ids
     *
     * @return QuestionQuiz[]
     */
    public function findByIdsOrdered(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $questions = $this->createQueryBuilder('question')
            ->andWhere('question.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($questions as $question) {
            $indexed[$question->getId()] = $question;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($indexed[$id])) {
                $ordered[] = $indexed[$id];
            }
        }

        return $ordered;
    }
}
