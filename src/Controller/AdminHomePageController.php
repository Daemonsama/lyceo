<?php

namespace App\Controller;

use App\Form\HomePageContentType;
use App\Repository\HomePageContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/page-accueil')]
final class AdminHomePageController extends AbstractController
{
    private const UPLOAD_MAX_BYTES = 5242880;

    /** @var list<string> */
    private const ALLOWED_IMAGE_EXT = ['jpg', 'png', 'webp', 'gif'];

    #[Route('', name: 'app_admin_home_page_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        HomePageContentRepository $homePageContentRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $homePage = $homePageContentRepository->getSingleton();
        $form = $this->createForm(HomePageContentType::class, $homePage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectDir = $this->getParameter('kernel.project_dir');
            $uploadDir = $projectDir.'/public/uploads/homepage';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $removeUpload = (bool) $form->get('removeUploadedAboutImage')->getData();
            /** @var UploadedFile|null $upload */
            $upload = $form->get('aboutImageFile')->getData();

            $normalizedExt = null;
            if ($upload instanceof UploadedFile) {
                $ext = strtolower((string) $upload->getClientOriginalExtension());
                if ($ext === 'jpeg') {
                    $ext = 'jpg';
                }
                if (!in_array($ext, self::ALLOWED_IMAGE_EXT, true)) {
                    $this->addFlash('error', 'Format accepté : JPEG, PNG, WebP ou GIF.');

                    return $this->render('admin_home_page/edit.html.twig', [
                        'form' => $form,
                    ]);
                }
                if ($upload->getSize() > self::UPLOAD_MAX_BYTES) {
                    $this->addFlash('error', 'Le fichier dépasse 5 Mo.');

                    return $this->render('admin_home_page/edit.html.twig', [
                        'form' => $form,
                    ]);
                }
                $normalizedExt = $ext;
            }

            if ($removeUpload && $homePage->hasUploadedAboutImage()) {
                $path = $uploadDir.'/'.$homePage->getAboutImageFilename();
                if (is_file($path)) {
                    unlink($path);
                }
                $homePage->setAboutImageFilename(null);
            }

            if ($upload instanceof UploadedFile && $normalizedExt !== null) {
                if ($homePage->hasUploadedAboutImage()) {
                    $old = $uploadDir.'/'.$homePage->getAboutImageFilename();
                    if (is_file($old)) {
                        unlink($old);
                    }
                }
                $filename = bin2hex(random_bytes(12)).'.'.$normalizedExt;
                $upload->move($uploadDir, $filename);
                $homePage->setAboutImageFilename($filename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'La page d\'accueil a été mise à jour.');

            return $this->redirect($this->generateUrl('app_admin_home_page_edit').'#photo');
        }

        return $this->render('admin_home_page/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
