<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\FormationUser;
use App\Entity\User;
use App\Form\RechercheType;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\FormationUserRepository;
use App\Services\ProgressionFormation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function app_formation(
        FormationRepository $formationRepository,
        SessionInterface $session,
        CategorieRepository $categorieRepository
    ): Response
    {
        $categorieId = $session->get("categorie");
        $categorie = $categorieId ? $categorieRepository->find($categorieId) : null;

        $form = $this->createForm(RechercheType::class, null, [
            'action' => $this->generateUrl('app_recherche')
        ]);

        if ($categorie) {
            $form->get('categorie')->setData($categorie);
            $formations = $formationRepository->findBy(['categorie' => $categorie]);
        } else {
            $formations = $formationRepository->findAll();
        }

        return $this->render('formation/index.html.twig', [
            'controller_name' => 'FormationController',
            'formations' => $formations,
            'rechercheForm' => $form,
        ]);
    }

    #[Route('/formation/{formation}/detail', name: 'app_formation_show')]
    #[IsGranted('ROLE_USER')]
    public function app_formation_show(
        Formation $formation,
        FormationUserRepository $formationUserRepository,
        ProgressionFormation $progression,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $chapitreEnCours = null;

        if ($user->hasFormation($formation)) {
            $formationUser = $formationUserRepository->findOneBy([
                'user' => $user,
                'formation' => $formation,
            ]);

            if ($formationUser) {
                $chapitreEnCours = $formationUser->getChapitreEncours();
            }
        }

        $formationTerminee = $user->hasFormation($formation)
            && $progression->hasCompletedFormation($user, $formation);

        $quizFinalEnAttente = $user->hasFormation($formation)
            && !$formationTerminee
            && $progression->needsFinalQuiz($user, $formation);

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
            'chapitreEnCours' => $chapitreEnCours,
            'progression' => $progression,
            'formationTerminee' => $formationTerminee,
            'quizFinalEnAttente' => $quizFinalEnAttente,
            'formationBadgeAsset' => $this->resolveFormationBadgeAsset(),
        ]);
    }

    #[Route('/formation/{formation}/acheter', name: 'app_formation_buy')]
    public function app_formation_buy(Formation $formation): Response
    {
        return $this->render('formation/buy.html.twig', [
            'formation' => $formation,
        ]);
    }
    #[Route('/formation/successful_payment', name: 'app_formation_successful_payment')]
    public function app_formation_successful_payment(): Response
    {
        return $this->render('formation/success.html.twig', []);
    }

    #[Route('/formation/{formation}/ipn', name: 'app_formation_ipn')]
    public function app_formation_ipn(Formation $formation, FormationUserRepository $formationUserRepository): Response
    {
        # TRAITEMENT ACHAT FORMATION (METTRE EN SERVICE pitié mvc)
        $user = $this->getUser();
        $formationUser = new FormationUser($user, $formation);
        $formationUserRepository->save($formationUser);
        return $this->redirectToRoute('app_formation_successful_payment', [
            'formation' => $formation,
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
