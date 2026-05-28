<?php

namespace App\Services;

use App\Entity\Formation;
use App\Entity\QuestionQuiz;
use App\Entity\User;
use App\Repository\QuestionQuizRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class QuizFinalService
{
    private const SESSION_KEY_PREFIX = 'quiz_final_question_ids_';
    private const MAX_FINAL_QUESTIONS = 20;

    public function __construct(
        private QuestionQuizRepository $questionQuizRepository,
        private RequestStack $requestStack,
    ) {}

    /**
     * @return QuestionQuiz[]
     */
    public function getAllValidQuestions(Formation $formation): array
    {
        return $this->questionQuizRepository->findValidQuestionsForFormation($formation);
    }

    /**
     * Tire aléatoirement des questions parmi tous les quiz des chapitres.
     *
     * @return QuestionQuiz[]
     */
    public function pickRandomQuestions(Formation $formation): array
    {
        $pool = $this->getAllValidQuestions($formation);
        if ($pool === []) {
            return [];
        }

        shuffle($pool);

        return array_slice($pool, 0, self::MAX_FINAL_QUESTIONS);
    }

    public function getSeuilReussite(Formation $formation): int
    {
        $seuils = [];
        foreach ($formation->getChapitres() as $chapitre) {
            $quiz = $chapitre->getQuiz();
            if ($quiz !== null && !$quiz->getQuestions()->isEmpty()) {
                $seuils[] = $quiz->getSeuilReussite();
            }
        }

        if ($seuils === []) {
            return 70;
        }

        return (int) round(array_sum($seuils) / count($seuils));
    }

    /**
     * @return QuestionQuiz[]
     */
    public function getOrCreateSessionQuestions(User $user, Formation $formation, bool $regenerate = false): array
    {
        $session = $this->getSession();
        $key = $this->sessionKey($formation);

        if (!$regenerate && $session->has($key)) {
            /** @var int[] $ids */
            $ids = $session->get($key);
            $questions = $this->questionQuizRepository->findByIdsOrdered($ids);
            if ($questions !== []) {
                return $questions;
            }
        }

        $questions = $this->pickRandomQuestions($formation);
        $ids = array_map(static fn (QuestionQuiz $q) => $q->getId(), $questions);
        $session->set($key, $ids);

        return $questions;
    }

    public function clearSessionQuestions(Formation $formation): void
    {
        $this->getSession()->remove($this->sessionKey($formation));
    }

    private function sessionKey(Formation $formation): string
    {
        return self::SESSION_KEY_PREFIX.$formation->getId();
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
