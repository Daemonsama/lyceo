<?php

namespace App\Service;

use App\Entity\Formation;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FormationApercuHandler
{
    private const MAX_BYTES = 5242880;

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(
        private FormationApercuStorage $storage,
    ) {
    }

    /**
     * @return string|null Message d'erreur, ou null si OK
     */
    public function applyFromForm(Formation $formation, FormInterface $form): ?string
    {
        $remove = (bool) $form->get('removeApercu')->getData();
        /** @var UploadedFile|null $upload */
        $upload = $form->get('apercuFile')->getData();

        if ($remove) {
            $this->storage->deleteFile($formation->getApercuFilename());
            $formation->setApercuFilename(null);

            return null;
        }

        if (!$upload instanceof UploadedFile) {
            return null;
        }

        if (!$upload->isValid()) {
            return $this->uploadErrorMessage($upload->getErrorMessage(), $upload->getError());
        }

        $ext = strtolower((string) $upload->getClientOriginalExtension());
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return 'Format accepté pour l\'aperçu : JPEG, PNG, WebP ou GIF.';
        }
        if ($upload->getSize() > self::MAX_BYTES) {
            return 'L\'image d\'aperçu dépasse 5 Mo.';
        }

        $this->storage->deleteFile($formation->getApercuFilename());

        $idPart = $formation->getId() ?? 'new';
        $filename = 'formation_'.$idPart.'_'.bin2hex(random_bytes(6)).'.'.$ext;

        $error = $this->storage->storeUploadedFile($upload, $filename);
        if ($error !== null) {
            return $error;
        }

        if (!$this->storage->fileExists($filename)) {
            $this->storage->deleteFile($filename);

            return 'L\'image a été reçue mais n\'a pas pu être enregistrée sur le disque. Réessayez ou contactez l\'administrateur.';
        }

        $formation->setApercuFilename($filename);

        return null;
    }

    public function deleteFile(Formation $formation): void
    {
        $this->storage->deleteFile($formation->getApercuFilename());
    }

    private function uploadErrorMessage(string $message, int $code): string
    {
        if ($code === \UPLOAD_ERR_INI_SIZE || $code === \UPLOAD_ERR_FORM_SIZE) {
            return 'Fichier trop volumineux (limite PHP : upload_max_filesize / post_max_size).';
        }
        if ($code === \UPLOAD_ERR_PARTIAL) {
            return 'Téléversement interrompu. Réessayez avec une image plus petite.';
        }
        if ($code === \UPLOAD_ERR_NO_TMP_DIR || $code === \UPLOAD_ERR_CANT_WRITE) {
            return 'Le serveur ne peut pas enregistrer le fichier temporaire. Vérifiez la configuration PHP.';
        }

        return 'Chargement de l\'image impossible : '.$message;
    }
}
