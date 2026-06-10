<?php

namespace App\Controller;

use App\Form\HomePromoBlockType;
use App\Repository\HomePromoBlockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/accueil-media')]
#[IsGranted('ROLE_SUPER_ADMIN')]
final class AdminHomePromoController extends AbstractController
{
    private const UPLOAD_MAX_BYTES = 209715200;

    /** @var list<string> */
    private const ALLOWED_VIDEO_EXT = ['mp4', 'webm', 'ogg', 'mov'];

    #[Route('', name: 'app_admin_home_promo', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        HomePromoBlockRepository $homePromoBlockRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $block = $homePromoBlockRepository->getSingleton();
        $form = $this->createForm(HomePromoBlockType::class, $block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectDir = $this->getParameter('kernel.project_dir');
            $uploadDir = $projectDir.'/public/uploads/promo';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $remove = (bool) $form->get('removeUploadedVideo')->getData();
            /** @var UploadedFile|null $upload */
            $upload = $form->get('videoFile')->getData();

            $normalizedExt = null;
            if ($upload instanceof UploadedFile) {
                $ext = strtolower((string) $upload->getClientOriginalExtension());
                if (!in_array($ext, self::ALLOWED_VIDEO_EXT, true)) {
                    $this->addFlash('error', 'Formats acceptés : MP4, WebM, OGG, MOV.');

                    return $this->render('admin_home_promo/edit.html.twig', [
                        'form' => $form,
                    ]);
                }
                if ($upload->getSize() > self::UPLOAD_MAX_BYTES) {
                    $this->addFlash('error', 'Le fichier dépasse 200 Mo.');

                    return $this->render('admin_home_promo/edit.html.twig', [
                        'form' => $form,
                    ]);
                }
                $normalizedExt = $ext;
            }

            if ($remove && $block->hasUploadedVideo()) {
                $path = $uploadDir.'/'.$block->getVideoFilename();
                if (is_file($path)) {
                    unlink($path);
                }
                $block->setVideoFilename(null);
            }

            if ($upload instanceof UploadedFile && $normalizedExt !== null) {
                if ($block->hasUploadedVideo()) {
                    $old = $uploadDir.'/'.$block->getVideoFilename();
                    if (is_file($old)) {
                        unlink($old);
                    }
                }
                $filename = bin2hex(random_bytes(12)).'.'.$normalizedExt;
                $upload->move($uploadDir, $filename);
                $block->setVideoFilename($filename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Le média d’accueil a été enregistré.');

            return $this->redirect($this->generateUrl('app_admin_home_promo').'#promo');
        }

        return $this->render('admin_home_promo/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
