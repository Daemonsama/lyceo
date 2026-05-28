<?php

namespace App\Service;

use App\Entity\HomePromoBlock;

/**
 * Détermine comment afficher le média sur l’accueil (fichier, YouTube, Vimeo, LinkedIn, URL directe).
 *
 * LinkedIn : pas d’iframe — la plateforme bloque souvent l’affichage intégré (frame-ancestors). On renvoie un lien à ouvrir dans un nouvel onglet.
 */
final class PromoVideoDisplayHelper
{
    /**
     * @return array<string, string>|null
     */
    public function resolve(HomePromoBlock $block): ?array
    {
        if ($block->hasUploadedVideo()) {
            return [
                'kind' => 'upload',
                'src' => 'uploads/promo/'.$block->getVideoFilename(),
            ];
        }

        $url = trim($block->getVideoUrl());
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        $id = $this->parseYouTubeId($url);
        if ($id !== null) {
            return [
                'kind' => 'youtube',
                'src' => 'https://www.youtube-nocookie.com/embed/'.$id,
            ];
        }

        $id = $this->parseVimeoId($url);
        if ($id !== null) {
            return [
                'kind' => 'vimeo',
                'src' => 'https://player.vimeo.com/video/'.$id,
            ];
        }

        if (preg_match('~linkedin\.com~i', $url)) {
            return [
                'kind' => 'linkedin',
                'href' => $this->normalizeLinkedInOpenUrl($url),
            ];
        }

        if (preg_match('~\.(mp4|webm|ogg)(\?[^\s]*)?$~i', $url)) {
            return [
                'kind' => 'direct',
                'src' => $url,
            ];
        }

        return [
            'kind' => 'iframe',
            'src' => $url,
        ];
    }

    private function parseYouTubeId(string $url): ?string
    {
        if (preg_match('~[?&]v=([a-zA-Z0-9_-]{11})\b~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~youtu\.be/([a-zA-Z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~youtube\.com/embed/([a-zA-Z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~youtube\.com/shorts/([a-zA-Z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    private function parseVimeoId(string $url): ?string
    {
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return $m[1];
        }

        return null;
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
