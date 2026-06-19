<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\User;
use App\Form\RechercheType;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\FormationUserRepository;
use App\Services\ProgressionFormation;
use App\Exception\InvalidPromoCodeException;
use App\Exception\StripePaymentException;
use App\Service\ChapitreMediaDisplayHelper;
use App\Service\StripePaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FormationController extends AbstractController
{
    #[Route('/formation/successful_payment', name: 'app_formation_successful_payment')]
    public function app_formation_successful_payment(Request $request): Response
    {
        $receipt = $request->getSession()->get('payment_receipt');
        $request->getSession()->remove('payment_receipt');

        if (!is_array($receipt)) {
            return $this->redirectToRoute('app_mon_profil');
        }

        return $this->render('formation/success.html.twig', [
            'receipt' => $receipt,
        ]);
    }

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
        ChapitreMediaDisplayHelper $mediaDisplayHelper,
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
            'presentationMediaDisplay' => $mediaDisplayHelper->resolveMediaReference($formation->getMedia()),
        ]);
    }

    #[Route('/formation/{formation}/acheter', name: 'app_formation_buy', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function app_formation_buy(
        Formation $formation,
        Request $request,
        StripePaymentService $stripePayment,
    ): Response {
        if (!$this->isCsrfTokenValid('formation_buy', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($user->hasFormation($formation)) {
            $this->addFlash('info', 'Vous possédez déjà ce module.');

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        $successUrl = $this->generateUrl(
            'app_formation_payment_success',
            ['formation' => $formation->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        ) . '?session_id={CHECKOUT_SESSION_ID}';

        $cancelUrl = $this->generateUrl(
            'app_formation_show',
            ['formation' => $formation->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $promoCode = trim($request->request->getString('promo_code'));

        try {
            $session = $stripePayment->createCheckoutSession(
                $formation,
                $user,
                $successUrl,
                $cancelUrl,
                $promoCode !== '' ? $promoCode : null,
            );
        } catch (InvalidPromoCodeException|StripePaymentException $e) {
            $this->addFlash('danger', $e->getMessage());
            if ($promoCode !== '') {
                $this->addFlash('promo_code', $promoCode);
            }

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        return $this->redirect($session->url);
    }

    #[Route('/formation/{formation}/payment/success', name: 'app_formation_payment_success')]
    #[IsGranted('ROLE_USER')]
    public function app_formation_payment_success(
        Formation $formation,
        Request $request,
        StripePaymentService $stripePayment,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $sessionId = $request->query->getString('session_id');
        if ($sessionId === '') {
            $this->addFlash('danger', 'Session de paiement invalide.');

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        $session = $stripePayment->retrieveCheckoutSession($sessionId, true);

        if (!$stripePayment->fulfillCheckoutSession($session, $user, $formation)) {
            $this->addFlash('danger', 'Le paiement n\'a pas pu être confirmé.');

            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);
        }

        $request->getSession()->set(
            'payment_receipt',
            $stripePayment->buildReceiptData($session, $formation, $user),
        );

        return $this->redirectToRoute('app_formation_successful_payment');
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
