<?php

namespace App\Service;

use App\Entity\Chapitre;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

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
            if (!$upload->isValid()) {
                return $this->uploadErrorMessage($upload->getErrorMessage(), $upload->getError());
            }

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

    /**
     * Détecte un échec silencieux (POST trop gros, limite PHP, fichier refusé avant Symfony).
     *
     * @return string|null Message d'erreur, ou null si OK
     */
    public function resolveUploadFailure(Request $request, FormInterface $form): ?string
    {
        if (!$form->isSubmitted()) {
            return null;
        }

        $contentLength = (int) $request->headers->get('Content-Length', 0);
        $postMax = $this->parseIniSize((string) ini_get('post_max_size'));
        if ($contentLength > 0 && $postMax > 0 && $contentLength > $postMax) {
            return sprintf(
                'Envoi trop volumineux pour le serveur (limite post_max_size : %s). Réduisez la taille de la vidéo ou augmentez la limite PHP.',
                ini_get('post_max_size')
            );
        }

        $formName = $form->getName();
        $fieldName = $form->get('media')->getName();
        $bag = $formName !== '' ? $request->files->get($formName) : $request->files->get($fieldName);
        if (!is_array($bag)) {
            $bag = $request->files->all();
        }

        $raw = is_array($bag) ? ($bag[$fieldName] ?? null) : null;
        if ($raw instanceof UploadedFile && !$raw->isValid()) {
            return $this->uploadErrorMessage($raw->getErrorMessage(), $raw->getError());
        }

        return null;
    }

    private function uploadErrorMessage(string $message, int $code): string
    {
        if ($code === \UPLOAD_ERR_INI_SIZE || $code === \UPLOAD_ERR_FORM_SIZE) {
            return sprintf(
                'Vidéo trop volumineuse pour le serveur (limite actuelle : upload_max_filesize=%s, post_max_size=%s). Contactez l\'administrateur ou utilisez un lien Google Drive.',
                ini_get('upload_max_filesize'),
                ini_get('post_max_size')
            );
        }
        if ($code === \UPLOAD_ERR_PARTIAL) {
            return 'Téléversement interrompu. Réessayez avec une connexion stable ou un fichier plus petit.';
        }
        if ($code === \UPLOAD_ERR_NO_TMP_DIR || $code === \UPLOAD_ERR_CANT_WRITE) {
            return 'Le serveur ne peut pas enregistrer le fichier temporaire. Vérifiez les droits d\'écriture PHP.';
        }

        return 'Chargement du média impossible : '.$message;
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
