<?php

namespace App\Services;

use App\Entity\Chapitre;
use App\Entity\Formation;
use App\Entity\FormationUser;
use App\Entity\QuestionQuiz;
use App\Entity\Quiz;
use App\Entity\User;
use App\Repository\FormationUserRepository;
use App\Repository\QuizFinalReussiRepository;
use App\Repository\QuizFinalTentativeRepository;
use App\Repository\QuizReussiRepository;
use App\Repository\QuizTentativeRepository;

class ProgressionFormation
{
    public const MAX_QUIZ_ATTEMPTS_BEFORE_SKIP = 2;

    public function __construct(
        private FormationUserRepository $formationUserRepository,
        private QuizReussiRepository $quizReussiRepository,
        private QuizTentativeRepository $quizTentativeRepository,
        private QuizFinalReussiRepository $quizFinalReussiRepository,
        private QuizFinalTentativeRepository $quizFinalTentativeRepository,
        private QuizFinalService $quizFinalService,
    ) {}

    public function getFormationUser(User $user, Formation $formation): ?FormationUser
    {
        if (!$user->hasFormation($formation)) {
            return null;
        }

        return $this->formationUserRepository->findOneBy([
            'user' => $user,
            'formation' => $formation,
        ]);
    }

    public function canAccessChapter(User $user, Chapitre $chapitre): bool
    {
        $formationUser = $this->getFormationUser($user, $chapitre->getFormation());
        if ($formationUser === null) {
            return false;
        }

        $current = $formationUser->getChapitreEncours();
        if ($current === null) {
            return $chapitre->getOrdre() === 1;
        }

        return $chapitre->getOrdre() <= $current->getOrdre();
    }

    public function hasPassedQuiz(User $user, Quiz $quiz): bool
    {
        return $this->quizReussiRepository->hasPassed($user, $quiz);
    }

    public function countFailedQuizAttempts(User $user, Quiz $quiz): int
    {
        return $this->quizTentativeRepository->countByUserAndQuiz($user, $quiz);
    }

    public function canSkipQuizAfterAttempts(User $user, Quiz $quiz): bool
    {
        return $this->countFailedQuizAttempts($user, $quiz) >= self::MAX_QUIZ_ATTEMPTS_BEFORE_SKIP;
    }

    public function needsQuizBeforeNext(User $user, Chapitre $chapitre): bool
    {
        $quiz = $chapitre->getQuiz();
        if ($quiz === null) {
            return false;
        }

        if ($quiz->getQuestions()->isEmpty()) {
            return false;
        }

        if ($this->hasPassedQuiz($user, $quiz)) {
            return false;
        }

        return !$this->canSkipQuizAfterAttempts($user, $quiz);
    }

    public function hasPassedFinalQuiz(User $user, Formation $formation): bool
    {
        return $this->quizFinalReussiRepository->hasPassed($user, $formation);
    }

    public function countFailedFinalQuizAttempts(User $user, Formation $formation): int
    {
        return $this->quizFinalTentativeRepository->countByUserAndFormation($user, $formation);
    }

    public function canSkipFinalQuizAfterAttempts(User $user, Formation $formation): bool
    {
        return $this->countFailedFinalQuizAttempts($user, $formation) >= self::MAX_QUIZ_ATTEMPTS_BEFORE_SKIP;
    }

    public function hasCompletedAllChapterQuizzes(User $user, Formation $formation): bool
    {
        foreach ($formation->getChapitres() as $chapitre) {
            $quiz = $chapitre->getQuiz();
            if ($quiz === null || $quiz->getQuestions()->isEmpty()) {
                continue;
            }

            if (!$this->hasPassedQuiz($user, $quiz) && !$this->canSkipQuizAfterAttempts($user, $quiz)) {
                return false;
            }
        }

        return true;
    }

    public function getLastChapter(Formation $formation): ?Chapitre
    {
        $last = null;
        foreach ($formation->getChapitres() as $chapitre) {
            if ($last === null || $chapitre->getOrdre() > $last->getOrdre()) {
                $last = $chapitre;
            }
        }

        return $last;
    }

    public function needsFinalQuiz(User $user, Formation $formation): bool
    {
        if ($this->hasPassedFinalQuiz($user, $formation)) {
            return false;
        }

        if (!$this->hasCompletedAllChapterQuizzes($user, $formation)) {
            return false;
        }

        return $this->quizFinalService->getAllValidQuestions($formation) !== [];
    }

    public function hasCompletedFormation(User $user, Formation $formation): bool
    {
        if (!$this->hasCompletedAllChapterQuizzes($user, $formation)) {
            return false;
        }

        if ($this->quizFinalService->getAllValidQuestions($formation) === []) {
            return true;
        }

        // Pour le quiz final, l'utilisateur doit réussir (pas de "skip" après X tentatives).
        return $this->hasPassedFinalQuiz($user, $formation);
    }

    public function calculateScore(Quiz $quiz, array $submittedAnswers): int
    {
        return $this->calculateScoreForQuestions($quiz->getQuestions()->toArray(), $submittedAnswers);
    }

    /**
     * @param QuestionQuiz[] $questions
     * @param array<string, mixed> $submittedAnswers
     */
    public function calculateScoreForQuestions(array $questions, array $submittedAnswers): int
    {
        if ($questions === []) {
            return 0;
        }

        $correct = 0;
        foreach ($questions as $question) {
            $questionId = (string) $question->getId();
            if (!isset($submittedAnswers[$questionId])) {
                continue;
            }

            $selectedId = (int) $submittedAnswers[$questionId];
            foreach ($question->getReponses() as $reponse) {
                if ($reponse->getId() === $selectedId && $reponse->isEstCorrecte()) {
                    $correct++;
                    break;
                }
            }
        }

        return (int) round(($correct / count($questions)) * 100);
    }

    /**
     * @param array<string, mixed> $submittedAnswers
     *
     * @return array<int, string|null>
     */
    public function buildReponseFeedback(Quiz $quiz, array $submittedAnswers, bool $showReveal): array
    {
        return $this->buildReponseFeedbackForQuestions(
            $quiz->getQuestions()->toArray(),
            $submittedAnswers,
            $showReveal
        );
    }

    /**
     * @param QuestionQuiz[] $questions
     * @param array<string, mixed> $submittedAnswers
     *
     * @return array<int, string|null>
     */
    public function buildReponseFeedbackForQuestions(array $questions, array $submittedAnswers, bool $showReveal): array
    {
        $states = [];

        foreach ($questions as $question) {
            $questionId = (string) $question->getId();
            $selectedId = isset($submittedAnswers[$questionId])
                ? (int) $submittedAnswers[$questionId]
                : null;

            foreach ($question->getReponses() as $reponse) {
                $reponseId = $reponse->getId();
                if ($reponseId === null) {
                    continue;
                }

                $isCorrect = $reponse->isEstCorrecte();
                $isSelected = $selectedId === $reponseId;

                if ($showReveal) {
                    if ($isCorrect) {
                        $states[$reponseId] = 'success';
                    } elseif ($isSelected) {
                        $states[$reponseId] = 'danger';
                    } else {
                        $states[$reponseId] = null;
                    }
                } elseif ($isSelected && $isCorrect) {
                    $states[$reponseId] = 'success';
                } elseif ($isSelected) {
                    $states[$reponseId] = 'danger';
                } else {
                    $states[$reponseId] = null;
                }
            }
        }

        return $states;
    }
}
