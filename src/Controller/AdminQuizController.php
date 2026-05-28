<?php

namespace App\Controller;

use App\Entity\Chapitre;
use App\Entity\Formation;
use App\Entity\QuestionQuiz;
use App\Entity\Quiz;
use App\Entity\ReponseQuiz;
use App\Form\QuestionQuizType;
use App\Form\QuizType;
use App\Repository\QuestionQuizRepository;
use App\Repository\QuizRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/quiz')]
final class AdminQuizController extends AbstractController
{
    #[Route('/formation/{formation}', name: 'app_admin_quiz_index', methods: ['GET'])]
    public function index(#[MapEntity(id: 'formation')] Formation $formation, QuizRepository $quizRepository): Response
    {
        $quizzes = $quizRepository->createQueryBuilder('q')
            ->join('q.chapitre', 'c')
            ->where('c.formation = :formation')
            ->setParameter('formation', $formation)
            ->orderBy('c.ordre', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin_quiz/index.html.twig', [
            'formation' => $formation,
            'quizzes' => $quizzes,
        ]);
    }

    #[Route('/new/{chapitre}', name: 'app_admin_quiz_new', methods: ['GET', 'POST'])]
    public function new(
        Chapitre $chapitre,
        Request $request,
        QuizRepository $quizRepository,
    ): Response {
        if ($chapitre->getQuiz() !== null) {
            $this->addFlash('warning', 'Ce chapitre possède déjà un quiz.');

            return $this->redirectToRoute('app_admin_formation_edit', [
                'formation' => $chapitre->getFormation()->getId(),
            ]);
        }

        $quiz = new Quiz($chapitre);
        $quiz->setTitre('Quiz – ' . $chapitre->getTitre());

        $form = $this->createForm(QuizType::class, $quiz, [
            'chapitre_choices' => [],
            'lock_chapitre' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chapitre->setQuiz($quiz);
            $quizRepository->save($quiz);
            $this->addFlash('success', 'Quiz créé. Ajoutez maintenant des questions.');

            return $this->redirectToRoute('app_admin_quiz_edit', ['quiz' => $quiz->getId()]);
        }

        return $this->render('admin_quiz/new.html.twig', [
            'chapitre' => $chapitre,
            'formation' => $chapitre->getFormation(),
            'form' => $form,
        ]);
    }

    #[Route('/{quiz}/edit', name: 'app_admin_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(Quiz $quiz, Request $request, QuizRepository $quizRepository): Response
    {
        $form = $this->createForm(QuizType::class, $quiz, [
            'chapitre_choices' => [],
            'lock_chapitre' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quizRepository->save($quiz);
            $this->addFlash('success', 'Quiz mis à jour.');

            return $this->redirectToRoute('app_admin_quiz_edit', ['quiz' => $quiz->getId()]);
        }

        return $this->render('admin_quiz/edit.html.twig', [
            'quiz' => $quiz,
            'formation' => $quiz->getFormation(),
            'form' => $form,
        ]);
    }

    #[Route('/{quiz}', name: 'app_admin_quiz_delete', methods: ['POST'])]
    public function delete(Request $request, Quiz $quiz, QuizRepository $quizRepository): Response
    {
        $formationId = $quiz->getFormation()->getId();

        if ($this->isCsrfTokenValid('delete' . $quiz->getId(), $request->getPayload()->getString('_token'))) {
            $quizRepository->remove($quiz);
            $this->addFlash('success', 'Quiz supprimé.');
        }

        return $this->redirectToRoute('app_admin_formation_edit', ['formation' => $formationId]);
    }

    #[Route('/{quiz}/question/new', name: 'app_admin_quiz_question_new', methods: ['GET', 'POST'])]
    public function newQuestion(
        Quiz $quiz,
        Request $request,
        QuestionQuizRepository $questionQuizRepository,
    ): Response {
        $question = new QuestionQuiz();
        $question->setOrdre($quiz->getQuestions()->count() + 1);

        for ($i = 0; $i < 4; $i++) {
            $question->addReponse(new ReponseQuiz());
        }

        $form = $this->createForm(QuestionQuizType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pruneEmptyReponses($question);
            if (!$this->validateQuestion($question)) {
                return $this->render('admin_quiz/question_form.html.twig', [
                    'quiz' => $quiz,
                    'form' => $form,
                    'question' => $question,
                    'is_new' => true,
                ]);
            }

            $quiz->addQuestion($question);
            $questionQuizRepository->save($question);
            $this->addFlash('success', 'Question ajoutée.');

            return $this->redirectToRoute('app_admin_quiz_edit', ['quiz' => $quiz->getId()]);
        }

        return $this->render('admin_quiz/question_form.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
            'question' => $question,
            'is_new' => true,
        ]);
    }

    #[Route('/question/{question}/edit', name: 'app_admin_quiz_question_edit', methods: ['GET', 'POST'])]
    public function editQuestion(
        QuestionQuiz $question,
        Request $request,
        QuestionQuizRepository $questionQuizRepository,
    ): Response {
        $quiz = $question->getQuiz();

        $form = $this->createForm(QuestionQuizType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->pruneEmptyReponses($question);
            if (!$this->validateQuestion($question)) {
                return $this->render('admin_quiz/question_form.html.twig', [
                    'quiz' => $quiz,
                    'form' => $form,
                    'question' => $question,
                    'is_new' => false,
                ]);
            }

            $questionQuizRepository->save($question);
            $this->addFlash('success', 'Question mise à jour.');

            return $this->redirectToRoute('app_admin_quiz_edit', ['quiz' => $quiz->getId()]);
        }

        return $this->render('admin_quiz/question_form.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
            'question' => $question,
            'is_new' => false,
        ]);
    }

    #[Route('/question/{question}', name: 'app_admin_quiz_question_delete', methods: ['POST'])]
    public function deleteQuestion(
        Request $request,
        QuestionQuiz $question,
        QuestionQuizRepository $questionQuizRepository,
    ): Response {
        $quizId = $question->getQuiz()->getId();

        if ($this->isCsrfTokenValid('delete' . $question->getId(), $request->getPayload()->getString('_token'))) {
            $questionQuizRepository->remove($question);
            $this->addFlash('success', 'Question supprimée.');
        }

        return $this->redirectToRoute('app_admin_quiz_edit', ['quiz' => $quizId]);
    }

    private function pruneEmptyReponses(QuestionQuiz $question): void
    {
        foreach ($question->getReponses()->toArray() as $reponse) {
            if (trim((string) $reponse->getLibelle()) === '') {
                $question->removeReponse($reponse);
            }
        }
    }

    private function validateQuestion(QuestionQuiz $question): bool
    {
        $reponses = $question->getReponses();

        if ($reponses->count() < 2) {
            $this->addFlash('error', 'Chaque question doit avoir au moins 2 réponses renseignées.');

            return false;
        }

        $correctCount = $reponses->filter(fn (ReponseQuiz $r) => $r->isEstCorrecte())->count();
        if ($correctCount !== 1) {
            $this->addFlash('error', 'Cochez exactement une bonne réponse.');

            return false;
        }

        return true;
    }
}
