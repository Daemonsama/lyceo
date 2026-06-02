<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Stocke les aperçus hors OneDrive si le projet est synchronisé (dossier souvent non inscriptible).
 */
final class FormationApercuStorage
{
    /** Chemin public (asset) des aperçus copiés dans public/medias/formations/. */
    public const PUBLIC_ASSET_PREFIX = 'medias/formations/';

    private readonly bool $useExternalStorage;
    private readonly string $storageDir;
    private readonly string $publicUrlPrefix;

    public function __construct(
        private string $projectDir,
        private string $uploadsDirectory,
    ) {
        $this->useExternalStorage = $this->detectOneDriveProject();
        if ($this->useExternalStorage) {
            $base = $this->resolveLocalAppDataDir();
            $this->storageDir = $base
                .\DIRECTORY_SEPARATOR.'Spcformation'
                .\DIRECTORY_SEPARATOR.'uploads'
                .\DIRECTORY_SEPARATOR.'formations';
            $this->publicUrlPrefix = '';
        } else {
            $this->storageDir = rtrim($uploadsDirectory, '/\\').\DIRECTORY_SEPARATOR.'formations';
            $this->publicUrlPrefix = 'medias/formations/';
        }
    }

    public function usesExternalStorage(): bool
    {
        return $this->useExternalStorage;
    }

    public function getStorageDir(): string
    {
        return $this->storageDir;
    }

    public function getPublicPathPrefix(): string
    {
        return $this->publicUrlPrefix;
    }

    public function getPublicAssetPath(string $filename): string
    {
        return self::PUBLIC_ASSET_PREFIX.$filename;
    }

    public function ensureStorageDir(): ?string
    {
        if (is_dir($this->storageDir)) {
            return null;
        }
        if (@mkdir($this->storageDir, 0777, true) || is_dir($this->storageDir)) {
            return null;
        }

        return 'Impossible de créer le dossier de stockage des aperçus.';
    }

    /**
     * @return string|null Message d'erreur
     */
    public function storeUploadedFile(UploadedFile $upload, string $filename): ?string
    {
        $error = $this->ensureStorageDir();
        if ($error !== null) {
            return $error;
        }

        try {
            $upload->move($this->storageDir, $filename);
        } catch (\Throwable $e) {
            return 'Impossible d\'enregistrer l\'image : '.$e->getMessage();
        }

        $this->mirrorToPublic($filename);

        return null;
    }

    public function getPublicMirrorDir(): string
    {
        return rtrim($this->uploadsDirectory, '/\\')
            .\DIRECTORY_SEPARATOR.'formations';
    }

    public function getPublicMirrorPath(string $filename): string
    {
        return $this->getPublicMirrorDir().\DIRECTORY_SEPARATOR.$filename;
    }

    public function hasPublicMirror(string $filename): bool
    {
        return is_file($this->getPublicMirrorPath($filename));
    }

    /**
     * Copie l’aperçu dans public/medias/formations/ pour un affichage direct (Apache, PHP built-in, etc.).
     */
    public function mirrorToPublic(string $filename): bool
    {
        $source = $this->resolveAbsolutePath($filename);
        if ($source === null) {
            return false;
        }

        $destDir = $this->getPublicMirrorDir();
        if (!is_dir($destDir) && !@mkdir($destDir, 0777, true) && !is_dir($destDir)) {
            return false;
        }

        $dest = $this->getPublicMirrorPath($filename);

        return @copy($source, $dest) && is_file($dest);
    }

    public function deleteFile(?string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }

        $path = $this->storageDir.\DIRECTORY_SEPARATOR.$filename;
        if (is_file($path)) {
            @unlink($path);
        }

        $mirror = $this->getPublicMirrorPath($filename);
        if (is_file($mirror)) {
            @unlink($mirror);
        }
    }

    public function fileExists(string $filename): bool
    {
        return $this->resolveAbsolutePath($filename) !== null;
    }

    public function getAbsolutePath(string $filename): ?string
    {
        return $this->resolveAbsolutePath($filename);
    }

    /**
     * Cherche le fichier dans le stockage actif puis dans d’éventuels anciens emplacements.
     */
    public function resolveAbsolutePath(string $filename): ?string
    {
        foreach ($this->candidatePaths($filename) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function candidatePaths(string $filename): array
    {
        $paths = [$this->storageDir.\DIRECTORY_SEPARATOR.$filename];

        $publicMedias = rtrim($this->uploadsDirectory, '/\\')
            .\DIRECTORY_SEPARATOR.'formations'
            .\DIRECTORY_SEPARATOR.$filename;
        $paths[] = $publicMedias;

        $legacyUploads = rtrim($this->projectDir, '/\\')
            .\DIRECTORY_SEPARATOR.'public'
            .\DIRECTORY_SEPARATOR.'uploads'
            .\DIRECTORY_SEPARATOR.'formations'
            .\DIRECTORY_SEPARATOR.$filename;
        $paths[] = $legacyUploads;

        return array_values(array_unique($paths));
    }

    private function detectOneDriveProject(): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return false;
        }

        return str_contains(str_replace('\\', '/', $this->projectDir), '/OneDrive/');
    }

    private function resolveLocalAppDataDir(): string
    {
        $localAppData = getenv('LOCALAPPDATA');
        if (is_string($localAppData) && $localAppData !== '') {
            return rtrim(str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, $localAppData), \DIRECTORY_SEPARATOR);
        }

        $userProfile = $_SERVER['USERPROFILE'] ?? getenv('USERPROFILE');
        if (is_string($userProfile) && $userProfile !== '') {
            return rtrim(str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, $userProfile), \DIRECTORY_SEPARATOR)
                .\DIRECTORY_SEPARATOR.'AppData'
                .\DIRECTORY_SEPARATOR.'Local';
        }

        return rtrim(sys_get_temp_dir(), \DIRECTORY_SEPARATOR);
    }
}
