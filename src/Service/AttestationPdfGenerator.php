<?php

namespace App\Service;

use App\Entity\Formation;
use App\Entity\QuizFinalReussi;
use App\Entity\User;
use App\Repository\HomePageContentRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class AttestationPdfGenerator
{
    public function __construct(
        private Environment $twig,
        private HomePageContentRepository $homePageContentRepository,
        private string $projectDir,
    ) {
    }

    public function createDownloadResponse(User $user, Formation $formation, QuizFinalReussi $quizFinalReussi): Response
    {
        $html = $this->twig->render('attestation/pdf.html.twig', [
            'user' => $user,
            'formation' => $formation,
            'quizFinalReussi' => $quizFinalReussi,
            'home' => $this->homePageContentRepository->getSingleton(),
            'badgeDataUri' => $this->resolveBadgeDataUri(),
            'logoDataUri' => $this->resolveLogoDataUri(),
            'companyName' => 'Stéphane Palmier Consulting (SPC)',
            'lieu' => 'Clermont-Ferrand',
            'reference' => $this->buildReference($user, $formation, $quizFinalReussi),
        ]);

        $filename = sprintf(
            'attestation-formation-%d-%s.pdf',
            $formation->getId() ?? 0,
            $quizFinalReussi->getDateReussite()->format('Y-m-d'),
        );

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Serif');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $content = $dompdf->output();

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($content),
        ]);
    }

    private function buildReference(User $user, Formation $formation, QuizFinalReussi $quizFinalReussi): string
    {
        return sprintf(
            'SPC-%s-F%d-U%d',
            $quizFinalReussi->getDateReussite()->format('Ymd'),
            $formation->getId() ?? 0,
            $user->getId() ?? 0,
        );
    }

    private function resolveBadgeDataUri(): ?string
    {
        return $this->resolvePublicImageDataUri([
            'public/badges/badge.png' => 'image/png',
            'public/badges/badge.jpg' => 'image/jpeg',
            'public/badges/badge.jpeg' => 'image/jpeg',
            'public/badges/badge.webp' => 'image/webp',
            'public/badge.png' => 'image/png',
            'public/medias/badge.png' => 'image/png',
        ]);
    }

    private function resolveLogoDataUri(): ?string
    {
        $home = $this->homePageContentRepository->getSingleton();
        $filename = $home->getAboutImageFilename();
        if ($filename !== null && $filename !== '') {
            $fromUpload = $this->resolvePublicImageDataUri([
                'public/uploads/homepage/'.$filename => $this->guessMime($filename),
            ]);
            if ($fromUpload !== null) {
                return $fromUpload;
            }
        }

        return $this->resolvePublicImageDataUri([
            'public/images/logo.png' => 'image/png',
            'public/images/logo.jpg' => 'image/jpeg',
            'public/medias/logo.png' => 'image/png',
        ]);
    }

    /**
     * @param array<string, string> $candidates
     */
    private function resolvePublicImageDataUri(array $candidates): ?string
    {
        foreach ($candidates as $relative => $mime) {
            $absolute = $this->projectDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (!is_file($absolute)) {
                continue;
            }

            $data = @file_get_contents($absolute);
            if ($data === false) {
                continue;
            }

            return 'data:'.$mime.';base64,'.base64_encode($data);
        }

        return null;
    }

    private function guessMime(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
    }

}
