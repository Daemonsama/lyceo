<?php

namespace App\Controller;

use App\Entity\Chapitre;
use App\Entity\Formation;
use App\Entity\User;
use App\Repository\FormationUserRepository;
use App\Service\ChapitreMediaDisplayHelper;
use App\Services\NextChap;
use App\Services\ProgressionFormation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class SuiviFormationController extends AbstractController
{
    #[Route('/suivi-formation/{chapitre}', name: 'app_suivi_formation')]
    public function app_suivi_formation(
        Chapitre $chapitre,
        FormationUserRepository $formationUserRepository,
        ProgressionFormation $progression,
        ChapitreMediaDisplayHelper $chapitreMediaDisplayHelper,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $formation = $chapitre->getFormation();

        if (!$user->hasFormation($formation)) {
            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        if (!$progression->canAccessChapter($user, $chapitre)) {
            $this->addFlash('warning', 'Ce chapitre n\'est pas encore accessible. Terminez les étapes précédentes.');

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        return $this->render('suivi_formation/read.html.twig', [
            'chapitre' => $chapitre,
            'userFormation' => $formationUserRepository->findOneBy(['user' => $user, 'formation' => $formation]),
            'needsQuiz' => $progression->needsQuizBeforeNext($user, $chapitre),
            'mediaDisplay' => $chapitreMediaDisplayHelper->resolve($chapitre),
        ]);
    }

    #[Route('/suivi-formation/{chapitre}/suivant', name: 'app_formation_chap_suivant')]
    public function app_formation_chap_suivant(
        Chapitre $chapitre,
        NextChap $nextChap,
        ProgressionFormation $progression,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $formation = $chapitre->getFormation();

        if (!$user->hasFormation($formation)) {
            return $this->redirectToRoute('app_formation_show', [
                'formation' => $formation->getId()
            ]);
        }

        $formationUser = $progression->getFormationUser($user, $formation);
        if ($formationUser?->getChapitreEncours()?->getId() !== $chapitre->getId()) {
            return $this->redirectToRoute('app_suivi_formation', [
                'chapitre' => $formationUser->getChapitreEncours()->getId(),
            ]);
        }

        if ($progression->needsQuizBeforeNext($user, $chapitre)) {
            return $this->redirectToRoute('app_quiz_formation', ['chapitre' => $chapitre->getId()]);
        }

        $chapSuivant = $nextChap->chapitre_suivant($user, $formation);

        if ($chapSuivant === null) {
            if ($progression->needsFinalQuiz($user, $formation)) {
                $this->addFlash('info', 'Bravo ! Passez maintenant le quiz final pour valider la formation.');

                return $this->redirectToRoute('app_quiz_final', [
                    'formation' => $formation->getId(),
                ]);
            }

            if ($progression->hasCompletedFormation($user, $formation)) {
                $this->addFlash('success', 'Félicitations ! Vous avez terminé la formation.');
            }

            return $this->redirectToRoute('app_formation_show', [
                'formation' => $formation->getId(),
            ]);
        }

        return $this->redirectToRoute('app_suivi_formation', [
            'chapitre' => $chapSuivant->getId()
        ]);
    }
}
