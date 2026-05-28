<?php

namespace App\Service;

use App\Entity\Chapitre;

/**
 * Détermine comment afficher le média d'un chapitre (fichier local, Google Drive, YouTube, Vimeo…).
 */
final class ChapitreMediaDisplayHelper
{
    private const VIDEO_EXTENSIONS = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];

    /**
     * @return array{kind: string, src?: string, href?: string}|null
     */
    public function resolve(Chapitre $chapitre): ?array
    {
        $mediaUrl = trim((string) $chapitre->getMediaUrl());
        if ($mediaUrl !== '') {
            return $this->resolveExternalUrl($mediaUrl);
        }

        $filename = $chapitre->getMedia();
        if ($filename === null || $filename === '') {
            return null;
        }

        $path = 'medias/'.$filename;
        if ($this->isVideoFilename($filename)) {
            return [
                'kind' => 'upload_video',
                'src' => $path,
            ];
        }

        return [
            'kind' => 'upload_image',
            'src' => $path,
        ];
    }

    /**
     * @return array{kind: string, src?: string, href?: string}|null
     */
    private function resolveExternalUrl(string $url): ?array
    {
        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        $driveEmbed = $this->parseGoogleDriveEmbedUrl($url);
        if ($driveEmbed !== null) {
            return [
                'kind' => 'drive',
                'src' => $driveEmbed,
            ];
        }

        $youtubeId = $this->parseYouTubeId($url);
        if ($youtubeId !== null) {
            return [
                'kind' => 'youtube',
                'src' => 'https://www.youtube-nocookie.com/embed/'.$youtubeId,
            ];
        }

        $vimeoId = $this->parseVimeoId($url);
        if ($vimeoId !== null) {
            return [
                'kind' => 'vimeo',
                'src' => 'https://player.vimeo.com/video/'.$vimeoId,
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

    public function parseGoogleDriveEmbedUrl(string $url): ?string
    {
        if (preg_match('~drive\.google\.com/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return 'https://drive.google.com/file/d/'.$m[1].'/preview';
        }
        if (preg_match('~drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)~', $url, $m)) {
            return 'https://drive.google.com/file/d/'.$m[1].'/preview';
        }
        if (preg_match('~drive\.google\.com/uc\?(?:export=(?:download|view)&)?id=([a-zA-Z0-9_-]+)~', $url, $m)) {
            return 'https://drive.google.com/file/d/'.$m[1].'/preview';
        }
        if (preg_match('~docs\.google\.com/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return 'https://drive.google.com/file/d/'.$m[1].'/preview';
        }

        return null;
    }

    public function isVideoFilename(string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($ext, self::VIDEO_EXTENSIONS, true);
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
}
