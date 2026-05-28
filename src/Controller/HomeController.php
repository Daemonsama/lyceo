<?php

namespace App\Controller;

use App\Repository\HomePageContentRepository;
use App\Repository\HomePromoBlockRepository;
use App\Service\PromoVideoDisplayHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        HomePageContentRepository $homePageContentRepository,
        HomePromoBlockRepository $homePromoBlockRepository,
        PromoVideoDisplayHelper $promoVideoDisplayHelper,
    ): Response {
        $promo = $homePromoBlockRepository->getSingleton();

        return $this->render('home/index.html.twig', [
            'home' => $homePageContentRepository->getSingleton(),
            'promo' => $promo,
            'promoPlayer' => $promoVideoDisplayHelper->resolve($promo),
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
