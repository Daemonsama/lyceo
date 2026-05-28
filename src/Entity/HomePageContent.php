<?php

namespace App\Entity;

use App\Repository\HomePageContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HomePageContentRepository::class)]
class HomePageContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $heroTitlePrefix = 'Développez votre';

    #[ORM\Column(length: 255)]
    private string $heroTitleHighlight = 'talent unique';

    #[ORM\Column(type: Types::TEXT)]
    private string $heroLead = 'Des formations professionnelles créatives et immersives pour gagner en efficacité et révéler votre plein potentiel.';

    #[ORM\Column(type: Types::TEXT)]
    private string $aboutImageUrl = 'https://static.wixstatic.com/media/bdddc0_685c5141770c4513b46a6a343cc7d091~mv2.jpg/v1/fill/w_443,h_431,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto/IMG_2866_JPG.jpg';

    /** Fichier enregistré dans public/uploads/homepage/ — prioritaire sur l’URL pour l’affichage. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $aboutImageFilename = null;

    /** Point focal horizontal (0–100 %), réglé à la souris dans l’admin. */
    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private int $aboutImagePanX = 50;

    /** Point focal vertical (0–100 %). */
    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    private int $aboutImagePanY = 50;

    /** Zoom affiché (100 = défaut, jusqu’à 250). */
    #[ORM\Column]
    #[Assert\Range(min: 100, max: 250)]
    private int $aboutImageZoom = 100;

    #[ORM\Column(length: 255)]
    private string $aboutName = 'Stéphane PALMIER';

    #[ORM\Column(length: 255)]
    private string $aboutRole = 'Coach professionnel certifié';

    #[ORM\Column(length: 255)]
    private string $aboutHeading = 'Ma passion : vous accompagner';

    #[ORM\Column(type: Types::TEXT)]
    private string $aboutParagraph1 = 'Ancien manager fort de <strong>25 ans d\'expérience</strong>, j\'ai entrepris une reconversion il y a 3 ans pour devenir <strong>formateur</strong> et <strong>coach professionnel certifié</strong>.';

    #[ORM\Column(type: Types::TEXT)]
    private string $aboutParagraph2 = 'J\'ai créé la SASU <strong>Stéphane PALMIER CONSULTING (SPC)</strong> avec un objectif clair : accompagner votre développement personnel et professionnel pour vous aider à atteindre votre plein potentiel.';

    #[ORM\Column(length: 255)]
    private string $aboutListTitle = 'Parcours & Expériences';

    #[ORM\Column(type: Types::TEXT)]
    private string $aboutListItems = "Manager (12 ans) à France Travail\nEnseignant à l'Université Clermont Auvergne\nConférencier\nPère de 4 enfants dont 3 sportifs de haut niveau";

    #[ORM\Column(length: 128)]
    private string $aboutBadge1 = 'Coach certifié';

    #[ORM\Column(length: 128)]
    private string $aboutBadge2 = 'Préparateur mental';

    #[ORM\Column(length: 128)]
    private string $aboutBadge3 = 'Formateur';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeroTitlePrefix(): string
    {
        return $this->heroTitlePrefix;
    }

    public function setHeroTitlePrefix(?string $heroTitlePrefix): static
    {
        $this->heroTitlePrefix = $heroTitlePrefix ?? '';

        return $this;
    }

    public function getHeroTitleHighlight(): string
    {
        return $this->heroTitleHighlight;
    }

    public function setHeroTitleHighlight(?string $heroTitleHighlight): static
    {
        $this->heroTitleHighlight = $heroTitleHighlight ?? '';

        return $this;
    }

    public function getHeroLead(): string
    {
        return $this->heroLead;
    }

    public function setHeroLead(?string $heroLead): static
    {
        $this->heroLead = $heroLead ?? '';

        return $this;
    }

    public function getAboutImageUrl(): string
    {
        return $this->aboutImageUrl;
    }

    public function setAboutImageUrl(?string $aboutImageUrl): static
    {
        $this->aboutImageUrl = $aboutImageUrl ?? '';

        return $this;
    }

    public function getAboutImageFilename(): ?string
    {
        return $this->aboutImageFilename;
    }

    public function setAboutImageFilename(?string $aboutImageFilename): static
    {
        $this->aboutImageFilename = $aboutImageFilename;

        return $this;
    }

    public function getAboutImagePanX(): int
    {
        return $this->aboutImagePanX;
    }

    public function setAboutImagePanX(int $aboutImagePanX): static
    {
        $this->aboutImagePanX = max(0, min(100, $aboutImagePanX));

        return $this;
    }

    public function getAboutImagePanY(): int
    {
        return $this->aboutImagePanY;
    }

    public function setAboutImagePanY(int $aboutImagePanY): static
    {
        $this->aboutImagePanY = max(0, min(100, $aboutImagePanY));

        return $this;
    }

    public function getAboutImageZoom(): int
    {
        return $this->aboutImageZoom;
    }

    public function setAboutImageZoom(int $aboutImageZoom): static
    {
        $this->aboutImageZoom = max(100, min(250, $aboutImageZoom));

        return $this;
    }

    /** true si une photo est stockée sur le serveur (prioritaire sur l’URL). */
    public function hasUploadedAboutImage(): bool
    {
        return $this->aboutImageFilename !== null && $this->aboutImageFilename !== '';
    }

    public function getAboutName(): string
    {
        return $this->aboutName;
    }

    public function setAboutName(?string $aboutName): static
    {
        $this->aboutName = $aboutName ?? '';

        return $this;
    }

    public function getAboutRole(): string
    {
        return $this->aboutRole;
    }

    public function setAboutRole(?string $aboutRole): static
    {
        $this->aboutRole = $aboutRole ?? '';

        return $this;
    }

    public function getAboutHeading(): string
    {
        return $this->aboutHeading;
    }

    public function setAboutHeading(?string $aboutHeading): static
    {
        $this->aboutHeading = $aboutHeading ?? '';

        return $this;
    }

    public function getAboutParagraph1(): string
    {
        return $this->aboutParagraph1;
    }

    public function setAboutParagraph1(?string $aboutParagraph1): static
    {
        $this->aboutParagraph1 = $aboutParagraph1 ?? '';

        return $this;
    }

    public function getAboutParagraph2(): string
    {
        return $this->aboutParagraph2;
    }

    public function setAboutParagraph2(?string $aboutParagraph2): static
    {
        $this->aboutParagraph2 = $aboutParagraph2 ?? '';

        return $this;
    }

    public function getAboutListTitle(): string
    {
        return $this->aboutListTitle;
    }

    public function setAboutListTitle(?string $aboutListTitle): static
    {
        $this->aboutListTitle = $aboutListTitle ?? '';

        return $this;
    }

    public function getAboutListItems(): string
    {
        return $this->aboutListItems;
    }

    public function setAboutListItems(?string $aboutListItems): static
    {
        $this->aboutListItems = $aboutListItems ?? '';

        return $this;
    }

    public function getAboutBadge1(): string
    {
        return $this->aboutBadge1;
    }

    public function setAboutBadge1(?string $aboutBadge1): static
    {
        $this->aboutBadge1 = $aboutBadge1 ?? '';

        return $this;
    }

    public function getAboutBadge2(): string
    {
        return $this->aboutBadge2;
    }

    public function setAboutBadge2(?string $aboutBadge2): static
    {
        $this->aboutBadge2 = $aboutBadge2 ?? '';

        return $this;
    }

    public function getAboutBadge3(): string
    {
        return $this->aboutBadge3;
    }

    public function setAboutBadge3(?string $aboutBadge3): static
    {
        $this->aboutBadge3 = $aboutBadge3 ?? '';

        return $this;
    }
}
