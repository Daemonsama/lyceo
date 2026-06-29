<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class SeoExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $siteUrl,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('seo_description', $this->truncateDescription(...), ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo_canonical_url', $this->getCanonicalUrl(...)),
            new TwigFunction('seo_absolute_asset', $this->getAbsoluteAssetUrl(...)),
            new TwigFunction('seo_site_url', $this->getSiteUrl(...)),
        ];
    }

    public function truncateDescription(?string $text, int $maxLength = 160): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $truncated = mb_substr($text, 0, $maxLength - 1);
        $lastSpace = mb_strrpos($truncated, ' ');
        if ($lastSpace !== false && $lastSpace > $maxLength - 40) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated, '.,;:-').'…';
    }

    public function getCanonicalUrl(?string $override = null): string
    {
        if ($override !== null && $override !== '') {
            return $this->toAbsoluteUrl($override);
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return rtrim($this->getSiteUrl(), '/').'/';
        }

        return $request->getSchemeAndHttpHost().$request->getPathInfo();
    }

    public function getAbsoluteAssetUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '//')) {
            $request = $this->requestStack->getCurrentRequest();
            $scheme = $request?->getScheme() ?? 'https';

            return $scheme.':'.$path;
        }

        return rtrim($this->getSiteUrl(), '/').'/'.ltrim($path, '/');
    }

    public function getSiteUrl(): string
    {
        if ($this->siteUrl !== '') {
            return rtrim($this->siteUrl, '/');
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request !== null) {
            return $request->getSchemeAndHttpHost();
        }

        return '';
    }

    private function toAbsoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim($this->getSiteUrl(), '/').'/'.ltrim($url, '/');
    }
}
