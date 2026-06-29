<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SeoController extends AbstractController
{
    #[Route('/robots.txt', name: 'app_robots', defaults: ['_format' => 'txt'])]
    public function robots(): Response
    {
        $sitemapUrl = $this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('seo/robots.txt.twig', [
            'sitemap_url' => $sitemapUrl,
        ], new Response('', 200, ['Content-Type' => 'text/plain; charset=UTF-8']));
    }

    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function sitemap(FormationRepository $formationRepository): Response
    {
        $formations = $formationRepository->findBy([], ['id' => 'ASC']);

        $urls = [
            [
                'loc' => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => $this->generateUrl('app_formation', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => $this->generateUrl('app_cgu', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
        ];

        foreach ($formations as $formation) {
            $urls[] = [
                'loc' => $this->generateUrl('app_formation_public', ['formation' => $formation->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        return $this->render('seo/sitemap.xml.twig', [
            'urls' => $urls,
        ], new Response('', 200, ['Content-Type' => 'application/xml; charset=UTF-8']));
    }
}
