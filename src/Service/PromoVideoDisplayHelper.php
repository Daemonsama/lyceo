<?php

namespace App\Service;

use App\Entity\HomePromoBlock;

/**
 * Détermine comment afficher le média sur l’accueil (fichier, YouTube, Vimeo, Google Drive, LinkedIn…).
 *
 * LinkedIn : pas d’iframe — la plateforme bloque souvent l’affichage intégré (frame-ancestors).
 */
final class PromoVideoDisplayHelper
{
    public function __construct(
        private string $projectDir,
        private ChapitreMediaDisplayHelper $mediaDisplayHelper,
    ) {}

    /**
     * @return array<string, string>|null
     */
    public function resolve(HomePromoBlock $block): ?array
    {
        if ($block->hasUploadedVideo()) {
            $filename = $block->getVideoFilename();
            if ($filename !== null && $this->uploadedFileExists($filename)) {
                return [
                    'kind' => 'upload',
                    'src' => 'uploads/promo/'.$filename,
                ];
            }
        }

        $url = trim($block->getVideoUrl());
        if ($url === '') {
            return null;
        }

        if (preg_match('~linkedin\.com~i', $url)) {
            return [
                'kind' => 'linkedin',
                'href' => $this->normalizeLinkedInOpenUrl($url),
            ];
        }

        $resolved = $this->mediaDisplayHelper->resolveMediaReference($url);
        if ($resolved === null) {
            return null;
        }

        if ($resolved['kind'] === 'upload_video' && isset($resolved['src'])) {
            return [
                'kind' => 'upload',
                'src' => $resolved['src'],
            ];
        }

        return $resolved;
    }

    public function uploadedFileExists(string $filename): bool
    {
        $filename = trim($filename);
        if ($filename === '' || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return false;
        }

        return is_file($this->promoUploadDir().'/'.$filename);
    }

    public function promoUploadDir(): string
    {
        return $this->projectDir.'/public/uploads/promo';
    }

    /** URL ouverte dans un nouvel onglet (évite le blocage iframe). */
    private function normalizeLinkedInOpenUrl(string $url): string
    {
        $u = trim($url);
        if (str_starts_with($u, '//')) {
            $u = 'https:'.$u;
        }
        if (!preg_match('~^https?://~i', $u)) {
            $u = 'https://'.$u;
        }

        if (preg_match('~linkedin\.com/embed/feed/update/(urn:li:[^\s?#]+)~i', $u, $m)) {
            return 'https://www.linkedin.com/feed/update/'.$m[1];
        }

        return $u;
    }
}
