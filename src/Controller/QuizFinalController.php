<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\QuizFinalReussi;
use App\Entity\QuizFinalTentative;
use App\Entity\User;
use App\Repository\QuizFinalReussiRepository;
use App\Repository\QuizFinalTentativeRepository;
use App\Services\ProgressionFormation;
use App\Services\QuizFinalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class QuizFinalController extends AbstractController
{
    #[Route('/formation/{formation}/quiz-final', name: 'app_quiz_final', methods: ['GET', 'POST'])]
    public function passerQuizFinal(
        Formation $formation,
        Request $request,
        ProgressionFormation $progression,
        QuizFinalService $quizFinalService,
        QuizFinalReussiRepository $quizFinalReussiRepository,
        QuizFinalTentativeRepository $quizFinalTentativeRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->hasFormation($formation)) {
            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        if ($progression->hasPassedFinalQuiz($user, $formation)) {
            $this->addFlash('success', 'Vous avez déjà validé le quiz final. Vous pouvez télécharger votre attestation.');

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        if (!$progression->hasCompletedAllChapterQuizzes($user, $formation)) {
            $this->addFlash('warning', 'Terminez d\'abord tous les chapitres et leurs quiz.');

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        $allQuestions = $quizFinalService->getAllValidQuestions($formation);
        if ($allQuestions === []) {
            $this->addFlash('info', 'Aucune question disponible pour le quiz final.');

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        $seuilReussite = $quizFinalService->getSeuilReussite($formation);
        $regenerate = $request->query->getBoolean('nouveau');
        $questions = $quizFinalService->getOrCreateSessionQuestions($user, $formation, $regenerate);

        $quizSubmitted = false;
        $passed = false;
        $score = null;
        $submittedAnswers = [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('quiz_final_' . $formation->getId(), $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }

            $submittedAnswers = $request->request->all('reponses');
            $score = $progression->calculateScoreForQuestions($questions, $submittedAnswers);
            $passed = $score >= $seuilReussite;
            $quizSubmitted = true;

            if ($passed) {
                $quizFinalReussiRepository->save(new QuizFinalReussi($user, $formation, $score));
                $quizFinalService->clearSessionQuestions($formation);
            } else {
                $quizFinalTentativeRepository->save(new QuizFinalTentative($user, $formation, $score));
            }
        }

        return $this->render('quiz_formation/passer_final.html.twig', [
            'formation' => $formation,
            'questions' => $questions,
            'totalQuestionsPool' => count($allQuestions),
            'seuilReussite' => $seuilReussite,
            'quizSubmitted' => $quizSubmitted,
            'passed' => $passed,
            'score' => $score,
            'submittedAnswers' => $submittedAnswers,
            'formationBadgeAsset' => $this->resolveFormationBadgeAsset(),
        ]);
    }

    private function resolveFormationBadgeAsset(): ?string
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $candidates = [
            ['public/badges/badge.png', 'badges/badge.png'],
            ['public/badges/badge.jpg', 'badges/badge.jpg'],
            ['public/badges/badge.jpeg', 'badges/badge.jpeg'],
            ['public/badges/badge.webp', 'badges/badge.webp'],
            ['public/badge.png', 'badge.png'],
            ['public/medias/badge.png', 'medias/badge.png'],
        ];

        foreach ($candidates as [$fsPath, $assetPath]) {
            if (is_file($projectDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $fsPath))) {
                return $assetPath;
            }
        }

        return null;
    }
}
