<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Form\ContactType;
use App\Repository\HomePageContentRepository;
use App\Repository\HomePromoBlockRepository;
use App\Service\ContactNotifier;
use App\Service\PromoVideoDisplayHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        HomePageContentRepository $homePageContentRepository,
        HomePromoBlockRepository $homePromoBlockRepository,
        PromoVideoDisplayHelper $promoVideoDisplayHelper,
        EntityManagerInterface $entityManager,
        ContactNotifier $contactNotifier,
    ): Response {
        $promo = $homePromoBlockRepository->getSingleton();

        $contactMessage = new ContactMessage();
        $contactForm = $this->createForm(ContactType::class, $contactMessage);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $contactMessage->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($contactMessage);
            $entityManager->flush();

            try {
                $contactNotifier->notify($contactMessage);
            } catch (\Throwable) {
                $this->addFlash('contact_warning', 'Votre message a été enregistré, mais l\'envoi de la notification par e-mail a échoué.');
            }

            $this->addFlash('contact_success', 'Merci ! Votre message a bien été envoyé. Nous vous recontacterons rapidement.');

            return $this->redirect($this->generateUrl('app_home').'#contact');
        }

        return $this->render('home/index.html.twig', [
            'home' => $homePageContentRepository->getSingleton(),
            'promo' => $promo,
            'promoPlayer' => $promoVideoDisplayHelper->resolve($promo),
            'contactForm' => $contactForm,
        ]);
    }

    #[Route('/admin', name: 'app_admin')]
    public function index_admin(): Response
    {
        return $this->render('home/admin.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
