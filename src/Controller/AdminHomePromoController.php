<?php

namespace App\Controller;

use App\Form\HomePromoBlockType;
use App\Repository\HomePromoBlockRepository;
use App\Service\PromoVideoDisplayHelper;
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

    public function __construct(
        private PromoVideoDisplayHelper $promoVideoDisplayHelper,
    ) {}

    #[Route('', name: 'app_admin_home_promo', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        HomePromoBlockRepository $homePromoBlockRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $block = $homePromoBlockRepository->getSingleton();
        $form = $this->createForm(HomePromoBlockType::class, $block);
        $form->handleRequest($request);

        $uploadError = null;
        if ($form->isSubmitted()) {
            $uploadError = $this->resolveVideoUploadFailure($request, $form->getName());
            if ($uploadError !== null) {
                $this->addFlash('error', $uploadError);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($uploadError !== null) {
                return $this->renderPromoForm($form);
            }

            $uploadDir = $this->promoVideoDisplayHelper->promoUploadDir();
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                    $this->addFlash('error', 'Impossible de créer le dossier public/uploads/promo.');

                    return $this->renderPromoForm($form);
                }
            }

            if ($block->hasUploadedVideo() && !$this->promoVideoDisplayHelper->uploadedFileExists((string) $block->getVideoFilename())) {
                $block->setVideoFilename(null);
            }

            $remove = (bool) $form->get('removeUploadedVideo')->getData();
            /** @var UploadedFile|null $upload */
            $upload = $form->get('videoFile')->getData();

            $normalizedExt = null;
            if ($upload instanceof UploadedFile) {
                if (!$upload->isValid()) {
                    $this->addFlash('error', $this->uploadErrorMessage($upload->getErrorMessage(), $upload->getError()));

                    return $this->renderPromoForm($form);
                }

                $ext = strtolower((string) $upload->getClientOriginalExtension());
                if (!in_array($ext, self::ALLOWED_VIDEO_EXT, true)) {
                    $this->addFlash('error', 'Formats acceptés : MP4, WebM, OGG, MOV.');

                    return $this->renderPromoForm($form);
                }
                if ($upload->getSize() > self::UPLOAD_MAX_BYTES) {
                    $this->addFlash('error', 'Le fichier dépasse 200 Mo.');

                    return $this->renderPromoForm($form);
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
                try {
                    $upload->move($uploadDir, $filename);
                } catch (\Throwable) {
                    $this->addFlash('error', 'Impossible d\'enregistrer la vidéo. Vérifiez les droits d\'écriture sur public/uploads/promo.');

                    return $this->renderPromoForm($form);
                }

                if (!$this->promoVideoDisplayHelper->uploadedFileExists($filename)) {
                    $this->addFlash('error', 'La vidéo n\'a pas pu être enregistrée sur le disque. Vérifiez la limite PHP upload_max_filesize (100 Mo recommandé).');

                    return $this->renderPromoForm($form);
                }

                $block->setVideoFilename($filename);
            }

            if (!$block->hasAnyVideo()) {
                $this->addFlash('warning', 'Aucune vidéo active : ajoutez un fichier ou un lien (YouTube, Google Drive, etc.).');
            }

            $entityManager->flush();
            $this->addFlash('success', 'Le média d’accueil a été enregistré.');

            return $this->redirect($this->generateUrl('app_admin_home_promo').'#promo');
        }

        return $this->renderPromoForm($form);
    }

    private function renderPromoForm(\Symfony\Component\Form\FormInterface $form): Response
    {
        return $this->render('admin_home_promo/edit.html.twig', [
            'form' => $form,
            'promoVideoDisplayHelper' => $this->promoVideoDisplayHelper,
        ]);
    }

    private function resolveVideoUploadFailure(Request $request, string $formName): ?string
    {
        $contentLength = (int) $request->headers->get('Content-Length', 0);
        $postMax = $this->parseIniSize((string) ini_get('post_max_size'));
        if ($contentLength > 0 && $postMax > 0 && $contentLength > $postMax) {
            return sprintf(
                'Envoi trop volumineux (limite post_max_size : %s). Relancez le serveur avec serve.bat ou réduisez la vidéo.',
                ini_get('post_max_size')
            );
        }

        $bag = $formName !== '' ? $request->files->get($formName) : null;
        if (!is_array($bag)) {
            return null;
        }

        $raw = $bag['videoFile'] ?? null;
        if ($raw instanceof UploadedFile && !$raw->isValid()) {
            return $this->uploadErrorMessage($raw->getErrorMessage(), $raw->getError());
        }

        return null;
    }

    private function uploadErrorMessage(string $message, int $code): string
    {
        if ($code === \UPLOAD_ERR_INI_SIZE || $code === \UPLOAD_ERR_FORM_SIZE) {
            return sprintf(
                'Vidéo trop volumineuse (limites PHP : upload_max_filesize=%s, post_max_size=%s). Utilisez serve.bat ou un lien YouTube / Google Drive.',
                ini_get('upload_max_filesize'),
                ini_get('post_max_size')
            );
        }
        if ($code === \UPLOAD_ERR_PARTIAL) {
            return 'Téléversement interrompu. Réessayez.';
        }
        if ($code === \UPLOAD_ERR_NO_TMP_DIR || $code === \UPLOAD_ERR_CANT_WRITE) {
            return 'Le serveur ne peut pas enregistrer le fichier temporaire.';
        }

        return 'Chargement de la vidéo impossible : '.$message;
    }

    private function parseIniSize(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
