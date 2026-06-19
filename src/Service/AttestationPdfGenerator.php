<?php

namespace App\Service;

use App\Entity\Formation;
use App\Entity\QuizFinalReussi;
use App\Entity\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class AttestationPdfGenerator
{
    public function __construct(
        private Environment $twig,
        private string $projectDir,
    ) {
    }

    public function createDownloadResponse(User $user, Formation $formation, QuizFinalReussi $quizFinalReussi): Response
    {
        $nomComplet = trim(
            Utf8Sanitizer::clean($user->getPrenom()).' '.Utf8Sanitizer::clean($user->getNom())
        );
        $formationTitre = Utf8Sanitizer::clean($formation->getTitre() ?? '');
        $filiere = Utf8Sanitizer::clean(
            $formation->getCategorie()?->getNom() ?? 'Module professionnel'
        );
        $dateReussite = $quizFinalReussi->getDateReussite();

        $fontsDir = str_replace('\\', '/', $this->projectDir.'/assets/fonts');
        $badgeAnneePath = $this->resolveBadgeAnneePath();
        $lyceoBrandLogoPath = $this->resolveImageDataUri(['lyceo-logo-brand.png', 'LYCEO 2.png']);
        $lyceoLogo1Path = $this->resolveImageDataUri(['logo1.png']);

        $html = $this->twig->render('attestation/pdf.html.twig', [
            'platformName' => Utf8Sanitizer::clean('Lyceo Campus'),
            'nomComplet' => $nomComplet,
            'formationTitre' => $formationTitre,
            'filiere' => $filiere,
            'nbChapitres' => $formation->getChapitres()->count(),
            'dateCertificatFr' => $dateReussite->format('d/m/Y'),
            'certYear' => $dateReussite->format('Y'),
            'fontRegular' => $fontsDir.'/Radley-Regular.ttf',
            'fontItalic' => $fontsDir.'/Radley-Italic.ttf',
            'badgeAnneePath' => $badgeAnneePath,
            'lyceoBrandLogoPath' => $lyceoBrandLogoPath,
            'lyceoLogo1Path' => $lyceoLogo1Path,
        ]);

        $html = Utf8Sanitizer::clean($html);

        $filename = sprintf(
            'attestation-formation-%d-%s.pdf',
            $formation->getId() ?? 0,
            $dateReussite->format('Y-m-d'),
        );

        $fontCacheDir = $this->projectDir.'/var/dompdf/fonts';
        if (!is_dir($fontCacheDir) && !mkdir($fontCacheDir, 0775, true) && !is_dir($fontCacheDir)) {
            throw new \RuntimeException('Impossible de créer le dossier cache Dompdf.');
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', false);
        $options->set('defaultFont', 'radley');
        $options->set('fontDir', $fontCacheDir);
        $options->set('fontCache', $fontCacheDir);
        $options->set('chroot', $this->projectDir);

        $dompdf = new Dompdf($options);

        $errorLevel = error_reporting(0);
        try {
            $this->ensureRadleyFonts($dompdf, $fontsDir);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $content = $dompdf->output();
        } finally {
            error_reporting($errorLevel);
        }

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($content),
        ]);
    }

    private function ensureRadleyFonts(Dompdf $dompdf, string $fontsDir): void
    {
        $fontMetrics = $dompdf->getFontMetrics();
        $families = $fontMetrics->getFontFamilies();

        if (!isset($families['radley'])) {
            $fontMetrics->registerFont(
                ['family' => 'Radley', 'style' => 'normal', 'weight' => 'normal'],
                $fontsDir.'/Radley-Regular.ttf',
            );
            $fontMetrics->registerFont(
                ['family' => 'Radley', 'style' => 'italic', 'weight' => 'normal'],
                $fontsDir.'/Radley-Italic.ttf',
            );
        }
    }

    private function resolveBadgeAnneePath(): ?string
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return null;
        }

        foreach (['annee.png', 'annee2.jpg'] as $filename) {
            $path = $this->projectDir.'/public/badges/'.$filename;
            if (is_readable($path)) {
                return $this->imageToTransparentDataUri($path);
            }
        }

        throw new \RuntimeException('Badge année introuvable (public/badges/annee.png).');
    }

    private function resolveImageDataUri(array $filenames): ?string
    {
        foreach ($filenames as $filename) {
            $path = $this->projectDir.'/public/image/'.$filename;
            if (!is_readable($path)) {
                continue;
            }

            if (!extension_loaded('gd') && !extension_loaded('imagick')) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return 'data:image/'.$ext.';base64,'.base64_encode((string) file_get_contents($path));
            }

            return $this->imageToTransparentDataUri($path);
        }

        return null;
    }

    private function imageToTransparentDataUri(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $image = match ($ext) {
            'png' => @imagecreatefrompng($path),
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            default => false,
        };

        if ($image === false) {
            $mime = str_ends_with($ext, 'png') ? 'image/png' : 'image/jpeg';

            return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
        }

        imagesavealpha($image, true);
        imagealphablending($image, false);

        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $rgba = imagecolorat($image, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if ($r >= 235 && $g >= 235 && $b >= 235) {
                    $alpha = 127;
                    if ($r < 252 || $g < 252 || $b < 252) {
                        $min = min($r, $g, $b);
                        $alpha = (int) round(127 * (252 - $min) / 17);
                    }
                    $color = imagecolorallocatealpha($image, $r, $g, $b, $alpha);
                    imagesetpixel($image, $x, $y, $color);
                }
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean() ?: '';
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
