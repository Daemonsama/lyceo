<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\User;
use App\Repository\QuizFinalReussiRepository;
use App\Service\AttestationPdfGenerator;
use App\Services\ProgressionFormation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AttestationController extends AbstractController
{
    #[Route('/formation/{formation}/attestation', name: 'app_formation_attestation', methods: ['GET'])]
    public function download(
        Formation $formation,
        ProgressionFormation $progression,
        QuizFinalReussiRepository $quizFinalReussiRepository,
        AttestationPdfGenerator $attestationPdfGenerator,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->hasFormation($formation)) {
            throw $this->createAccessDeniedException();
        }

        if (!$progression->hasPassedFinalQuiz($user, $formation)) {
            $this->addFlash('warning', 'Vous devez réussir le quiz final pour télécharger l\'attestation.');

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        $quizFinalReussi = $quizFinalReussiRepository->findOneByUserAndFormation($user, $formation);
        if ($quizFinalReussi === null) {
            throw $this->createNotFoundException('Attestation introuvable.');
        }

        try {
            return $attestationPdfGenerator->createDownloadResponse($user, $formation, $quizFinalReussi);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de générer le PDF : '.$e->getMessage());

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }
    }
}
