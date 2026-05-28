<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MonProfilController extends AbstractController
{

    #[Route('/profil', name: 'app_mon_profil')]
    public function index(): Response
    {
        return $this->render('mon_profil/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
