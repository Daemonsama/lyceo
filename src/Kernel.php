<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Sur Windows, les projets dans OneDrive verrouillent souvent var/cache (lecture seule / sync).
     * On déplace cache et logs vers %LOCALAPPDATA%\Spcformation\.
     */
    private function useLocalAppDataStorage(): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return false;
        }

        $custom = $_SERVER['APP_CACHE_DIR'] ?? $_ENV['APP_CACHE_DIR'] ?? null;
        if ($custom !== null && $custom !== '') {
            return true;
        }

        return str_contains(str_replace('\\', '/', $this->getProjectDir()), '/OneDrive/');
    }

    private function getLocalAppDataBaseDir(): string
    {
        $custom = $_SERVER['APP_CACHE_DIR'] ?? $_ENV['APP_CACHE_DIR'] ?? null;
        if ($custom !== null && $custom !== '') {
            return rtrim(str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, $custom), \DIRECTORY_SEPARATOR);
        }

        $projectSlug = basename(str_replace('\\', '/', $this->getProjectDir()));

        return $this->resolveWindowsLocalAppDataDir()
            .\DIRECTORY_SEPARATOR.'Spcformation'
            .\DIRECTORY_SEPARATOR.$projectSlug;
    }

    /**
     * Évite C:\Windows\Spcformation quand LOCALAPPDATA est absent (CLI lancé sans profil utilisateur).
     */
    private function resolveWindowsLocalAppDataDir(): string
    {
        $localAppData = getenv('LOCALAPPDATA');
        if (is_string($localAppData) && $localAppData !== '' && !$this->isUnsafeWindowsSystemDir($localAppData)) {
            return rtrim(str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, $localAppData), \DIRECTORY_SEPARATOR);
        }

        $userProfile = $_SERVER['USERPROFILE'] ?? $_ENV['USERPROFILE'] ?? getenv('USERPROFILE');
        if (is_string($userProfile) && $userProfile !== '') {
            $fromProfile = rtrim(str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, $userProfile), \DIRECTORY_SEPARATOR)
                .\DIRECTORY_SEPARATOR.'AppData'
                .\DIRECTORY_SEPARATOR.'Local';
            if (is_dir($fromProfile) || @mkdir($fromProfile, 0777, true) || is_dir($fromProfile)) {
                return $fromProfile;
            }
        }

        return rtrim(sys_get_temp_dir(), \DIRECTORY_SEPARATOR);
    }

    private function isUnsafeWindowsSystemDir(string $path): bool
    {
        $normalized = strtolower(rtrim(str_replace(['/', '\\'], \DIRECTORY_SEPARATOR, $path), \DIRECTORY_SEPARATOR));

        return in_array($normalized, ['c:'.\DIRECTORY_SEPARATOR.'windows', 'c:'.\DIRECTORY_SEPARATOR.'winnt'], true);
    }

    private function ensureWritableDir(string $path): string
    {
        if (!is_dir($path) && !@mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Impossible de créer le dossier "%s".', $path));
        }

        return $path;
    }

    public function getCacheDir(): string
    {
        if (!$this->useLocalAppDataStorage()) {
            return parent::getCacheDir();
        }

        $dir = $this->ensureWritableDir(
            $this->getLocalAppDataBaseDir().\DIRECTORY_SEPARATOR.'cache'.\DIRECTORY_SEPARATOR.$this->environment
        );

        return $dir;
    }

    public function getLogDir(): string
    {
        if (!$this->useLocalAppDataStorage()) {
            return parent::getLogDir();
        }

        return $this->ensureWritableDir(
            $this->getLocalAppDataBaseDir().\DIRECTORY_SEPARATOR.'log'
        );
    }
}
