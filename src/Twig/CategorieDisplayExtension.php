<?php

namespace App\Twig;

use App\Entity\Categorie;
use App\Service\CategorieDisplayHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CategorieDisplayExtension extends AbstractExtension
{
    public function __construct(
        private readonly CategorieDisplayHelper $categorieDisplayHelper,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('categorie_visual', $this->getVisual(...)),
        ];
    }

    /**
     * @return array{slug: string, label: string}
     */
    public function getVisual(Categorie $categorie): array
    {
        return $this->categorieDisplayHelper->resolve($categorie);
    }
}
