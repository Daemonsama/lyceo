<?php

namespace App\Service;

use App\Entity\Chapitre;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ChapitreMediaHandler
{
    public const MAX_BYTES = 104857600; // 100 Mo

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'mp4', 'webm', 'ogg', 'mov', 'm4v',
    ];

    public function __construct(
        private ChapitreMediaDisplayHelper $displayHelper,
    ) {}

    /**
     * @return string|null Message d'erreur, ou null si OK
     */
    public function validateHasContent(Chapitre $chapitre, FormInterface $form): ?string
    {
        $hasFile = $form->get('media')->getData() instanceof UploadedFile;
        $hasUrl = trim((string) $chapitre->getMediaUrl()) !== '';
        $hasLocalMedia = $chapitre->getMedia() !== null && $chapitre->getMedia() !== '';
        $remove = (bool) $form->get('removeMedia')->getData();

        if ($chapitre->hasTexteContenu() || $hasFile || $hasUrl) {
            return null;
        }

        if ($hasLocalMedia && !$remove) {
            return null;
        }

        return 'Ajoutez au moins un contenu : texte, fichier média ou lien vidéo (vous pouvez combiner plusieurs).';
    }

    /**
     * @return string|null Message d'erreur, ou null si OK
     */
    public function applyFromForm(Chapitre $chapitre, FormInterface $form, string $uploadsDirectory): ?string
    {
        $remove = (bool) $form->get('removeMedia')->getData();
        /** @var UploadedFile|null $upload */
        $upload = $form->get('media')->getData();
        $mediaUrl = trim((string) $form->get('mediaUrl')->getData());

        if ($remove) {
            $this->deleteLocalFile($chapitre, $uploadsDirectory);
            $chapitre->setMedia(null);
            $chapitre->setMediaUrl(null);

            return null;
        }

        if ($upload instanceof UploadedFile) {
            $ext = strtolower((string) $upload->getClientOriginalExtension());
            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                return 'Formats acceptés : JPG, PNG, GIF, WebP, MP4, WebM, OGG, MOV.';
            }
            if ($upload->getSize() > self::MAX_BYTES) {
                return 'Le fichier dépasse 100 Mo.';
            }

            $dirError = $this->ensureUploadDirectory($uploadsDirectory);
            if ($dirError !== null) {
                return $dirError;
            }

            $this->deleteLocalFile($chapitre, $uploadsDirectory);

            $idPart = $chapitre->getId() ?? 'new';
            $filename = 'chapitre_'.$idPart.'_'.bin2hex(random_bytes(6)).'.'.$ext;
            try {
                $upload->move($uploadsDirectory, $filename);
            } catch (\Throwable $e) {
                return 'Impossible d\'enregistrer le fichier. Vérifiez que le dossier public/medias est accessible en écriture sur le serveur.';
            }
            $chapitre->setMedia($filename);
            $chapitre->setMediaUrl(null);

            return null;
        }

        if ($mediaUrl !== '') {
            if ($this->displayHelper->parseGoogleDriveEmbedUrl($mediaUrl) === null
                && !preg_match('~^https?://~i', $mediaUrl)
                && !str_starts_with($mediaUrl, '//')
            ) {
                return 'Lien invalide. Utilisez une URL complète (https://…), par ex. un lien de partage Google Drive.';
            }

            $this->deleteLocalFile($chapitre, $uploadsDirectory);
            $chapitre->setMedia(null);
            $chapitre->setMediaUrl($mediaUrl);

            return null;
        }

        return null;
    }

    public function ensureUploadDirectory(string $uploadsDirectory): ?string
    {
        if (!is_dir($uploadsDirectory)) {
            if (!@mkdir($uploadsDirectory, 0775, true) && !is_dir($uploadsDirectory)) {
                return sprintf(
                    'Le dossier %s n\'existe pas et n\'a pas pu être créé. Créez-le manuellement ou retirez l\'attribut « Lecture seule » du projet.',
                    $uploadsDirectory
                );
            }
        }

        if (!is_writable($uploadsDirectory)) {
            $resolved = realpath($uploadsDirectory) ?: $uploadsDirectory;

            return sprintf(
                'Le dossier %s n\'est pas accessible en écriture. Sur Windows : clic droit → Propriétés → décocher « Lecture seule », puis réessayer.',
                $resolved
            );
        }

        return null;
    }

    public function deleteLocalFile(Chapitre $chapitre, string $uploadsDirectory): void
    {
        $media = $chapitre->getMedia();
        if ($media === null || $media === '') {
            return;
        }

        $path = rtrim($uploadsDirectory, '/\\').'/'.$media;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
