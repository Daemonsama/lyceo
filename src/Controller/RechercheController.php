<?php

namespace App\Controller;

use App\Form\RechercheType;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RechercheController extends AbstractController
{
    #[Route('/recherche', name: 'app_recherche', methods: ['POST'])]
    public function app_recherche(
        SessionInterface $session,
        Request $request
    ): Response
    {
        $form = $this->createForm(RechercheType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder la catégorie en session
            $categorie = $form->get("categorie")->getData();
            $session->set("categorie", $categorie?->getId());

            // Rediriger vers la liste des formations
            return $this->redirectToRoute('app_formation', [], Response::HTTP_SEE_OTHER);
        }

        // Si le formulaire n'est pas soumis, rediriger aussi
        return $this->redirectToRoute('app_formation', [], Response::HTTP_SEE_OTHER);
    }

}
