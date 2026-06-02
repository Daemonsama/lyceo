<?php

namespace App\Twig;

use App\Entity\Formation;
use App\Service\FormationApercuDisplayHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FormationApercuExtension extends AbstractExtension
{
    public function __construct(
        private FormationApercuDisplayHelper $apercuDisplayHelper,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('formation_apercu_url', $this->getApercuUrl(...)),
        ];
    }

    public function getApercuUrl(Formation $formation): string
    {
        return $this->apercuDisplayHelper->getUrl($formation);
    }
}
