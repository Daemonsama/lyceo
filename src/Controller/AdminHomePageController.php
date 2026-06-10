<?php

namespace App\Controller;

use App\Entity\HomePageContent;
use App\Form\HomePageContentType;
use App\Repository\HomePageContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/page-accueil')]
#[IsGranted('ROLE_SUPER_ADMIN')]
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

            $imageError = $this->handleImageUpload(
                $form->get('aboutImageFile')->getData(),
                (bool) $form->get('removeUploadedAboutImage')->getData(),
                $homePage,
                $uploadDir,
                static fn (HomePageContent $h) => $h->getAboutImageFilename(),
                static fn (HomePageContent $h, ?string $f) => $h->setAboutImageFilename($f),
                static fn (HomePageContent $h) => $h->hasUploadedAboutImage(),
            );
            if ($imageError !== null) {
                $this->addFlash('error', $imageError);

                return $this->render('admin_home_page/edit.html.twig', ['form' => $form]);
            }

            $entityManager->flush();
            $this->addFlash('success', 'La page d\'accueil a été mise à jour.');

            return $this->redirect($this->generateUrl('app_admin_home_page_edit').'#section-hero');
        }

        return $this->render('admin_home_page/edit.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @param list<string> $extraExt
     */
    private function handleImageUpload(
        mixed $upload,
        bool $remove,
        HomePageContent $homePage,
        string $uploadDir,
        callable $getFilename,
        callable $setFilename,
        callable $hasFile,
        array $extraExt = [],
    ): ?string {
        $allowed = array_merge(self::ALLOWED_IMAGE_EXT, $extraExt);

        if ($remove && $hasFile($homePage)) {
            $path = $uploadDir.'/'.$getFilename($homePage);
            if (is_file($path)) {
                unlink($path);
            }
            $setFilename($homePage, null);
        }

        if (!$upload instanceof UploadedFile) {
            return null;
        }

        $ext = strtolower((string) $upload->getClientOriginalExtension());
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if (!in_array($ext, $allowed, true)) {
            return 'Format accepté : '.implode(', ', array_map('strtoupper', $allowed)).'.';
        }
        if ($upload->getSize() > self::UPLOAD_MAX_BYTES) {
            return 'Le fichier dépasse 5 Mo.';
        }

        if ($hasFile($homePage)) {
            $old = $uploadDir.'/'.$getFilename($homePage);
            if (is_file($old)) {
                unlink($old);
            }
        }

        $filename = bin2hex(random_bytes(12)).'.'.$ext;
        $upload->move($uploadDir, $filename);
        $setFilename($homePage, $filename);

        return null;
    }
}
