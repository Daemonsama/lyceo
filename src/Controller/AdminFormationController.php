<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use App\Service\ChapitreMediaDisplayHelper;
use App\Service\FormationApercuDisplayHelper;
use App\Service\FormationApercuHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/formation')]
final class AdminFormationController extends AbstractController
{
    public function __construct(
        private FormationApercuHandler $apercuHandler,
        private FormationApercuDisplayHelper $apercuDisplayHelper,
    ) {
    }

    #[Route(name: 'app_admin_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository): Response
    {
        return $this->render('admin_formation/index.html.twig', [
            'formations' => $formationRepository->findAll(),
            'apercuDisplayHelper' => $this->apercuDisplayHelper,
        ]);
    }

    #[Route('/new', name: 'app_admin_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FormationRepository $formationRepository): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->flashFormErrors($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $formationRepository->save($formation);

            $error = $this->apercuHandler->applyFromForm($formation, $form);
            if ($error !== null) {
                $this->addFlash('error', $error);

                return $this->render('admin_formation/new.html.twig', [
                    'formation' => $formation,
                    'form' => $form,
                    'apercuDisplayHelper' => $this->apercuDisplayHelper,
                ]);
            }

            if ($formation->hasApercu()) {
                $formationRepository->save($formation);
            }

            $this->addFlash('success', 'Formation créée.');

            return $this->redirectToRoute('app_admin_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'apercuDisplayHelper' => $this->apercuDisplayHelper,
        ]);
    }

    #[Route('/{formation}', name: 'app_admin_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('admin_formation/show.html.twig', [
            'formation' => $formation,
            'apercuDisplayHelper' => $this->apercuDisplayHelper,
        ]);
    }

    #[Route('/{formation}/edit', name: 'app_admin_formation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Formation $formation,
        FormationRepository $formationRepository,
        ChapitreMediaDisplayHelper $mediaHelper,
    ): Response {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->flashFormErrors($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $error = $this->apercuHandler->applyFromForm($formation, $form);
            if ($error !== null) {
                $this->addFlash('error', $error);

                return $this->render('admin_formation/edit.html.twig', [
                    'formation' => $formation,
                    'form' => $form,
                    'mediaHelper' => $mediaHelper,
                    'apercuDisplayHelper' => $this->apercuDisplayHelper,
                ]);
            }

            $formationRepository->save($formation);
            $this->addFlash('success', 'Formation mise à jour.');

            return $this->redirectToRoute('app_admin_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_formation/edit.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'mediaHelper' => $mediaHelper,
            'apercuDisplayHelper' => $this->apercuDisplayHelper,
        ]);
    }

    #[Route('/{formation}', name: 'app_admin_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, FormationRepository $formationRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$formation->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Réessayez.');

            return $this->redirectToRoute('app_admin_formation_edit', ['formation' => $formation->getId()]);
        }

        try {
            $this->apercuHandler->deleteFile($formation);
            $formationRepository->delete($formation);
            $this->addFlash('success', 'Formation supprimée.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de supprimer la formation : '.$e->getMessage());
        }

        return $this->redirectToRoute('app_admin_formation_index', [], Response::HTTP_SEE_OTHER);
    }

    private function flashFormErrors(\Symfony\Component\Form\FormInterface $form): void
    {
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
    }
}
