<?php

namespace App\Service;

use App\Entity\Categorie;

final class CategorieDisplayHelper
{
    /** @var array<string, array{slug: string, label: string}> */
    private const PRESETS = [
        'management' => ['slug' => 'management', 'label' => 'Management'],
        'coaching' => ['slug' => 'coaching', 'label' => 'Coaching'],
        'leadership' => ['slug' => 'leadership', 'label' => 'Leadership'],
        'communication' => ['slug' => 'communication', 'label' => 'Communication'],
        'formation' => ['slug' => 'formation', 'label' => 'Module'],
        'marketing' => ['slug' => 'marketing', 'label' => 'Marketing'],
        'rh' => ['slug' => 'rh', 'label' => 'Ressources humaines'],
        'finance' => ['slug' => 'finance', 'label' => 'Finance'],
    ];

    /** @var list<string> */
    private const FALLBACK_SLUGS = [
        'management',
        'coaching',
        'leadership',
        'communication',
        'formation',
    ];

    /**
     * @return array{slug: string, label: string}
     */
    public function resolve(Categorie $categorie): array
    {
        $nom = mb_strtolower(trim((string) $categorie->getNom()));
        $normalized = preg_replace('/[^a-z0-9]+/u', ' ', $nom) ?? '';
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? '');

        foreach (self::PRESETS as $keyword => $preset) {
            if ($normalized !== '' && str_contains($normalized, $keyword)) {
                return $preset;
            }
        }

        $index = max(0, ((int) $categorie->getId()) - 1) % count(self::FALLBACK_SLUGS);
        $slug = self::FALLBACK_SLUGS[$index];

        return [
            'slug' => $slug,
            'label' => (string) $categorie->getNom(),
        ];
    }
}
