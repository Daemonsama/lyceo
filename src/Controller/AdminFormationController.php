<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\FormationPromoCode;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use App\Service\ChapitreMediaDisplayHelper;
use App\Service\FormationApercuDisplayHelper;
use App\Service\FormationApercuHandler;
use App\Service\StripePromoCodeSyncService;
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
    public function new(
        Request $request,
        FormationRepository $formationRepository,
        StripePromoCodeSyncService $promoCodeSync,
    ): Response {
        $formation = new Formation();
        $formation->addPromoCode(new FormationPromoCode());
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->flashFormErrors($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->removeEmptyPromoCodes($formation);
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

            $this->syncPromoCodes($promoCodeSync, $formation);
            $this->addFlash('success', 'Module créé.');

            return $this->redirectToRoute('app_admin_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'apercuDisplayHelper' => $this->apercuDisplayHelper,
        ]);
    }

    #[Route('/{formation}', name: 'app_admin_formation_show', methods: ['GET'])]
    public function show(Formation $formation, ChapitreMediaDisplayHelper $mediaHelper): Response
    {
        return $this->render('admin_formation/show.html.twig', [
            'formation' => $formation,
            'apercuDisplayHelper' => $this->apercuDisplayHelper,
            'mediaHelper' => $mediaHelper,
        ]);
    }

    #[Route('/{formation}/edit', name: 'app_admin_formation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Formation $formation,
        FormationRepository $formationRepository,
        ChapitreMediaDisplayHelper $mediaHelper,
        StripePromoCodeSyncService $promoCodeSync,
    ): Response {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->flashFormErrors($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->removeEmptyPromoCodes($formation);

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
            $this->syncPromoCodes($promoCodeSync, $formation);
            $this->addFlash('success', 'Module mis à jour.');

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
            $this->addFlash('success', 'Module supprimé.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de supprimer le module : '.$e->getMessage());
        }

        return $this->redirectToRoute('app_admin_formation_index', [], Response::HTTP_SEE_OTHER);
    }

    private function flashFormErrors(\Symfony\Component\Form\FormInterface $form): void
    {
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
    }

    private function removeEmptyPromoCodes(Formation $formation): void
    {
        foreach ($formation->getPromoCodes()->toArray() as $promoCode) {
            $code = $promoCode->getCode();
            if ($code === null || trim($code) === '') {
                $formation->removePromoCode($promoCode);
            }
        }
    }

    private function syncPromoCodes(StripePromoCodeSyncService $promoCodeSync, Formation $formation): void
    {
        if ($formation->getPromoCodes()->isEmpty()) {
            return;
        }

        $errors = $promoCodeSync->syncFormation($formation);

        if ($errors === []) {
            $this->addFlash('success', 'Codes promo synchronisés avec Stripe.');

            return;
        }

        foreach ($errors as $error) {
            $this->addFlash('warning', $error);
        }
    }
}
