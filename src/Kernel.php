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

        $localAppData = getenv('LOCALAPPDATA') ?: sys_get_temp_dir();

        return $localAppData.\DIRECTORY_SEPARATOR.'Spcformation';
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
