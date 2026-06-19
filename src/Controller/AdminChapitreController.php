<?php

namespace App\Controller;

use App\Entity\Chapitre;
use App\Entity\Formation;
use App\Form\ChapitreType;
use App\Repository\ChapitreRepository;
use App\Service\ChapitreMediaDisplayHelper;
use App\Service\ChapitreMediaHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/chapitre')]
final class AdminChapitreController extends AbstractController
{
    public function __construct(
        private ChapitreMediaHandler $chapitreMediaHandler,
        private ChapitreMediaDisplayHelper $chapitreMediaDisplayHelper,
    ) {}

    #[Route(name: 'app_admin_chapitre_index', methods: ['GET'])]
    public function index(#[MapEntity(id: 'formation')] Formation $formation): Response
    {
        return $this->render('admin_chapitre/index.html.twig', [
            'formation' => $formation,
            'chapitres' => $formation->getChapitres(),
            'mediaHelper' => $this->chapitreMediaDisplayHelper,
        ]);
    }

    #[Route('/new/{formation}', name: 'app_admin_chapitre_new', methods: ['GET', 'POST'])]
    public function new(
        #[MapEntity(id: 'formation')] Formation $formation,
        Request $request,
        ChapitreRepository $chapitreRepository,
    ): Response {
        $chapitre = new Chapitre($formation);
        $form = $this->createForm(ChapitreType::class, $chapitre);
        $form->handleRequest($request);

        $uploadError = null;
        if ($form->isSubmitted()) {
            $uploadError = $this->chapitreMediaHandler->resolveUploadFailure($request, $form);
            if ($uploadError !== null) {
                $this->addFlash('error', $uploadError);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($uploadError !== null) {
                return $this->render('admin_chapitre/new.html.twig', [
                    'formation' => $formation,
                    'form' => $form,
                    'chapitre' => $chapitre,
                    'mediaDisplay' => $this->chapitreMediaDisplayHelper->resolve($chapitre),
                ]);
            }

            $contentError = $this->chapitreMediaHandler->validateHasContent($chapitre, $form);
            if ($contentError !== null) {
                $this->addFlash('error', $contentError);

                return $this->render('admin_chapitre/new.html.twig', [
                    'formation' => $formation,
                    'form' => $form,
                    'chapitre' => $chapitre,
                    'mediaDisplay' => $this->chapitreMediaDisplayHelper->resolve($chapitre),
                ]);
            }

            $chapitreRepository->save($chapitre);

            $error = $this->chapitreMediaHandler->applyFromForm(
                $chapitre,
                $form,
                $this->getParameter('uploads_directory'),
            );
            if ($error !== null) {
                $this->addFlash('error', $error);
            } else {
                $chapitreRepository->save($chapitre);
            }

            return $this->redirectToRoute('app_admin_formation_edit', [
                'formation' => $formation->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_chapitre/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'chapitre' => $chapitre,
            'mediaDisplay' => $this->chapitreMediaDisplayHelper->resolve($chapitre),
        ]);
    }

    #[Route('/{chapitre}', name: 'app_admin_chapitre_show', methods: ['GET'])]
    public function show(Chapitre $chapitre): Response
    {
        return $this->render('admin_chapitre/show.html.twig', [
            'chapitre' => $chapitre,
            'mediaDisplay' => $this->chapitreMediaDisplayHelper->resolve($chapitre),
        ]);
    }

    #[Route('/{chapitre}/edit', name: 'app_admin_chapitre_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Chapitre $chapitre,
        ChapitreRepository $chapitreRepository,
    ): Response {
        $form = $this->createForm(ChapitreType::class, $chapitre);
        $form->handleRequest($request);

        $uploadError = null;
        if ($form->isSubmitted()) {
            $uploadError = $this->chapitreMediaHandler->resolveUploadFailure($request, $form);
            if ($uploadError !== null) {
                $this->addFlash('error', $uploadError);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($uploadError !== null) {
                return $this->render('admin_chapitre/edit.html.twig', [
                    'chapitre' => $chapitre,
                    'form' => $form,
                    'mediaDisplay' => $this->chapitreMediaDisplayHelper->resolve($chapitre),
                ]);
            }

            $contentError = $this->chapitreMediaHandler->validateHasContent($chapitre, $form);
            if ($contentError !== null) {
                $this->addFlash('error', $contentError);

                return $this->render('admin_chapitre/edit.html.twig', [
                    'chapitre' => $chapitre,
                    'form' => $form,
                    'mediaDisplay' => $this->chapitreMediaDisplayHelper->resolve($chapitre),
                ]);
            }

            $error = $this->chapitreMediaHandler->applyFromForm(
                $chapitre,
                $form,
                $this->getParameter('uploads_directory'),
            );
            if ($error !== null) {
                $this->addFlash('error', $error);

                return $this->render('admin_chapitre/edit.html.twig', [
                    'chapitre' => $chapitre,
                    'form' => $form,
                    'mediaDisplay' => $this->chapitreMediaDisplayHelper->resolve($chapitre),
                ]);
            }

            $chapitreRepository->save($chapitre);

            return $this->redirectToRoute('app_admin_formation_edit', [
                'formation' => $chapitre->getFormation()->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_chapitre/edit.html.twig', [
            'chapitre' => $chapitre,
            'form' => $form,
            'mediaDisplay' => $this->chapitreMediaDisplayHelper->resolve($chapitre),
        ]);
    }

    #[Route('/{chapitre}', name: 'app_admin_chapitre_delete', methods: ['POST'])]
    public function delete(Request $request, Chapitre $chapitre, ChapitreRepository $chapitreRepository): Response
    {
        $formation = $chapitre->getFormation();
        if ($this->isCsrfTokenValid('delete'.$chapitre->getId(), $request->request->getString('_token'))) {
            $this->chapitreMediaHandler->deleteLocalFile($chapitre, $this->getParameter('uploads_directory'));
            $chapitreRepository->remove($chapitre);
        }

        return $this->redirectToRoute('app_admin_formation_edit', [
            'formation' => $formation->getId(),
        ], Response::HTTP_SEE_OTHER);
    }
}
