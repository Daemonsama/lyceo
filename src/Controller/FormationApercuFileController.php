<?php

namespace App\Controller;

use App\Service\FormationApercuStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class FormationApercuFileController extends AbstractController
{
    #[Route('/fichiers/formation-apercu/{filename}', name: 'app_formation_apercu_file', requirements: ['filename' => '[A-Za-z0-9._-]+'])]
    public function serve(string $filename, FormationApercuStorage $storage): Response
    {
        if (!$storage->usesExternalStorage()) {
            throw new NotFoundHttpException();
        }

        $path = $storage->resolveAbsolutePath($filename);
        if ($path === null) {
            throw new NotFoundHttpException();
        }

        return (new BinaryFileResponse($path))->setPublic();
    }
}
