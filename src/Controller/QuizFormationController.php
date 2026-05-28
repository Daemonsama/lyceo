<?php

namespace App\Controller;

use App\Entity\Chapitre;
use App\Entity\QuizReussi;
use App\Entity\QuizTentative;
use App\Entity\User;
use App\Repository\QuizReussiRepository;
use App\Repository\QuizTentativeRepository;
use App\Services\ProgressionFormation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class QuizFormationController extends AbstractController
{
    #[Route('/suivi-formation/{chapitre}/quiz', name: 'app_quiz_formation', methods: ['GET', 'POST'])]
    public function passerQuiz(
        Chapitre $chapitre,
        Request $request,
        ProgressionFormation $progression,
        QuizReussiRepository $quizReussiRepository,
        QuizTentativeRepository $quizTentativeRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $formation = $chapitre->getFormation();

        if (!$user->hasFormation($formation) || !$progression->canAccessChapter($user, $chapitre)) {
            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        $quiz = $chapitre->getQuiz();
        if ($quiz === null || $quiz->getQuestions()->isEmpty()) {
            return $this->redirectToRoute('app_formation_chap_suivant', ['chapitre' => $chapitre->getId()]);
        }

        if ($progression->hasPassedQuiz($user, $quiz)) {
            return $this->redirectToRoute('app_formation_chap_suivant', ['chapitre' => $chapitre->getId()]);
        }

        $formationUser = $progression->getFormationUser($user, $formation);
        if ($formationUser?->getChapitreEncours()?->getId() !== $chapitre->getId()) {
            $this->addFlash('warning', 'Terminez d\'abord le chapitre en cours avant de passer le quiz.');

            return $this->redirectToRoute('app_suivi_formation', ['chapitre' => $formationUser->getChapitreEncours()->getId()]);
        }

        $failedAttempts = $progression->countFailedQuizAttempts($user, $quiz);
        $canSkipToNextChapter = $progression->canSkipQuizAfterAttempts($user, $quiz);

        $quizSubmitted = false;
        $passed = false;
        $score = null;
        $submittedAnswers = [];
        $reponseStates = [];
        $showReveal = false;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('quiz' . $quiz->getId(), $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }

            $submittedAnswers = $request->request->all('reponses');
            $score = $progression->calculateScore($quiz, $submittedAnswers);
            $passed = $score >= $quiz->getSeuilReussite();
            $quizSubmitted = true;

            if ($passed) {
                $quizReussiRepository->save(new QuizReussi($user, $quiz, $score));
            } else {
                $quizTentativeRepository->save(new QuizTentative($user, $quiz, $score));
                $failedAttempts = $progression->countFailedQuizAttempts($user, $quiz);
                $canSkipToNextChapter = $progression->canSkipQuizAfterAttempts($user, $quiz);
                $showReveal = $canSkipToNextChapter;
            }

            $reponseStates = $progression->buildReponseFeedback($quiz, $submittedAnswers, $showReveal);
        }

        return $this->render('quiz_formation/passer.html.twig', [
            'chapitre' => $chapitre,
            'quiz' => $quiz,
            'formation' => $formation,
            'quizSubmitted' => $quizSubmitted,
            'passed' => $passed,
            'score' => $score,
            'submittedAnswers' => $submittedAnswers,
            'reponseStates' => $reponseStates,
            'showReveal' => $showReveal,
            'failedAttempts' => $failedAttempts,
            'maxAttemptsBeforeReveal' => ProgressionFormation::MAX_QUIZ_ATTEMPTS_BEFORE_SKIP,
            'canSkipToNextChapter' => $canSkipToNextChapter,
        ]);
    }
}
