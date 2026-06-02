<?php

namespace App\Service;

use App\Entity\Formation;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FormationApercuDisplayHelper
{
    /** Chemin asset de l’aperçu par défaut (logo SPC). */
    public const DEFAULT_APERCU_ASSET = 'image/logo1.png';

    public function __construct(
        private Packages $packages,
        private FormationApercuStorage $storage,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getUrl(Formation $formation): string
    {
        if (!$formation->hasApercu()) {
            return $this->getDefaultUrl();
        }

        $filename = $formation->getApercuFilename();
        if (!$this->storage->fileExists($filename)) {
            return $this->getDefaultUrl();
        }

        if (!$this->storage->hasPublicMirror($filename)) {
            $this->storage->mirrorToPublic($filename);
        }

        if ($this->storage->hasPublicMirror($filename)) {
            return $this->packages->getUrl($this->storage->getPublicAssetPath($filename));
        }

        if ($this->storage->usesExternalStorage()) {
            return $this->urlGenerator->generate('app_formation_apercu_file', [
                'filename' => $filename,
            ]);
        }

        return $this->packages->getUrl($this->storage->getPublicAssetPath($filename));
    }

    public function getDefaultUrl(): string
    {
        return $this->packages->getUrl(self::DEFAULT_APERCU_ASSET);
    }
}
