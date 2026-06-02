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

    public function app_formation_successful_payment(): Response

    {

        return $this->render('formation/success.html.twig', []);

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

            $this->addFlash('info', 'Vous possédez déjà cette formation.');



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

        } catch (InvalidPromoCodeException $e) {

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



        $session = $stripePayment->retrieveCheckoutSession($sessionId);



        if (!$stripePayment->fulfillCheckoutSession($session, $user, $formation)) {

            $this->addFlash('danger', 'Le paiement n\'a pas pu être confirmé.');



            return $this->redirectToRoute('app_formation_show', ['formation' => $formation->getId()]);

        }



        return $this->redirectToRoute('app_formation_successful_payment');

    }

}

