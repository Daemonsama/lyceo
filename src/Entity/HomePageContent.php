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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroLogoFilename = null;

    #[ORM\Column(length: 128)]
    private string $heroCtaPrimaryLabel = 'Découvrir les formations';

    #[ORM\Column(length: 255)]
    private string $heroCtaPrimaryUrl = '/formation';

    #[ORM\Column(length: 128)]
    private string $heroCtaSecondaryLabel = 'Nous contacter';

    #[ORM\Column(length: 255)]
    private string $heroCtaSecondaryUrl = '#contact';

    #[ORM\Column(length: 64)]
    private string $stat1Value = '25+';

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $stat1Icon = null;

    #[ORM\Column(length: 255)]
    private string $stat1Label = 'Années d\'expérience';

    #[ORM\Column(length: 64)]
    private string $stat2Value = '100%';

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $stat2Icon = null;

    #[ORM\Column(length: 255)]
    private string $stat2Label = 'Formations interactives';

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $stat3Value = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $stat3Icon = 'star-fill';

    #[ORM\Column(length: 255)]
    private string $stat3Label = 'Coach certifié';

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $stat4Value = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $stat4Icon = 'geo-alt-fill';

    #[ORM\Column(length: 255)]
    private string $stat4Label = 'Aurillac (15)';

    #[ORM\Column(length: 255)]
    private string $missionTitle = 'Notre mission';

    #[ORM\Column(type: Types::TEXT)]
    private string $missionLead = 'Vous accompagner dans le développement de votre <strong>AGILITÉ</strong>, votre <strong>CARE</strong>, votre <strong>FLOW</strong> et votre <strong>ENGAGEMENT</strong>.';

    #[ORM\Column(type: Types::TEXT)]
    private string $missionBody = 'Nous vous délivrons les outils nécessaires pour repenser et déployer votre mission en tant que <strong>coach manager</strong> ou membre d\'équipe. Apprenez à communiquer efficacement, maîtrisez la gestion hybride et créez de la valeur partagée.';

    #[ORM\Column(length: 255)]
    private string $missionCardTitle = 'Nos objectifs';

    #[ORM\Column(type: Types::TEXT)]
    private string $missionListItems = "Développer votre efficacité professionnelle\nRévéler votre talent unique et votre potentiel\nAccompagner votre équipe vers l'excellence\nCréer de la valeur partagée dans votre entreprise";

    #[ORM\Column(length: 255)]
    private string $audienceTitle = 'Pour qui ?';

    #[ORM\Column(type: Types::TEXT)]
    private string $audienceLead = 'Nos formations s\'adressent aux équipes et managers qui veulent progresser';

    #[ORM\Column(length: 255)]
    private string $audienceCard1Title = 'Formation INTRA';

    #[ORM\Column(type: Types::TEXT)]
    private string $audienceCard1Text = 'Pour votre équipe ou vos managers de différents services au sein de votre entreprise.';

    #[ORM\Column(length: 64)]
    private string $audienceCard1Icon = 'building';

    #[ORM\Column(length: 255)]
    private string $audienceCard2Title = 'Formation INTER';

    #[ORM\Column(type: Types::TEXT)]
    private string $audienceCard2Text = 'Pour managers et équipes de différentes entreprises qui veulent partager leurs expériences.';

    #[ORM\Column(length: 64)]
    private string $audienceCard2Icon = 'people';

    #[ORM\Column(length: 255)]
    private string $servicesTitle = 'Nos services';

    #[ORM\Column(type: Types::TEXT)]
    private string $servicesLead = 'Un accompagnement complet pour votre développement';

    #[ORM\Column(length: 255)]
    private string $service1Title = 'Coaching Individualisé';

    #[ORM\Column(type: Types::TEXT)]
    private string $service1Text = 'Des séances personnalisées pour identifier et surmonter vos obstacles spécifiques.';

    #[ORM\Column(length: 64)]
    private string $service1Icon = 'person-check';

    #[ORM\Column(length: 255)]
    private string $service2Title = 'Formations Pratiques';

    #[ORM\Column(type: Types::TEXT)]
    private string $service2Text = 'Des programmes concrets et interactifs pour acquérir des compétences rapidement.';

    #[ORM\Column(length: 64)]
    private string $service2Icon = 'book';

    #[ORM\Column(length: 255)]
    private string $service3Title = 'Objectifs Mesurables';

    #[ORM\Column(type: Types::TEXT)]
    private string $service3Text = 'Établissement d\'objectifs clairs pour suivre vos progrès et célébrer vos réussites.';

    #[ORM\Column(length: 64)]
    private string $service3Icon = 'graph-up-arrow';

    #[ORM\Column(length: 255)]
    private string $contactTitle = 'Nous contacter';

    #[ORM\Column(type: Types::TEXT)]
    private string $contactLead = 'Une question sur nos formations ? Écrivez-nous, nous vous répondrons dans les meilleurs délais.';

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

    #[ORM\Column(length: 128)]
    private string $aboutBadge4 = '';

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

    public function getAboutBadge4(): string
    {
        return $this->aboutBadge4;
    }

    public function setAboutBadge4(?string $aboutBadge4): static
    {
        $this->aboutBadge4 = $aboutBadge4 ?? '';

        return $this;
    }

    public function isStatSlotFilled(int $index): bool
    {
        if ($index < 1 || $index > 4) {
            return false;
        }

        $label = trim($this->{'getStat'.$index.'Label'}());
        $value = trim((string) ($this->{'getStat'.$index.'Value'}() ?? ''));
        $icon = trim((string) ($this->{'getStat'.$index.'Icon'}() ?? ''));

        return $label !== '' || $value !== '' || $icon !== '';
    }

    /**
     * @return list<array{text: string, icon: string|null}>
     */
    public function getAboutBadgesForDisplay(): array
    {
        $defaultIcons = ['award', null, 'lightbulb', 'star-fill'];
        $badges = [];

        for ($i = 1; $i <= 4; ++$i) {
            $text = trim($this->{'getAboutBadge'.$i}());
            if ($text === '') {
                continue;
            }

            $badges[] = [
                'text' => $text,
                'icon' => $defaultIcons[$i - 1],
            ];
        }

        return $badges;
    }

    public function hasUploadedHeroLogo(): bool
    {
        return $this->heroLogoFilename !== null && $this->heroLogoFilename !== '';
    }

    public function getHeroLogoFilename(): ?string
    {
        return $this->heroLogoFilename;
    }

    public function setHeroLogoFilename(?string $heroLogoFilename): static
    {
        $this->heroLogoFilename = $heroLogoFilename;

        return $this;
    }

    public function getHeroCtaPrimaryLabel(): string
    {
        return $this->heroCtaPrimaryLabel;
    }

    public function setHeroCtaPrimaryLabel(?string $heroCtaPrimaryLabel): static
    {
        $this->heroCtaPrimaryLabel = $heroCtaPrimaryLabel ?? '';

        return $this;
    }

    public function getHeroCtaPrimaryUrl(): string
    {
        return $this->heroCtaPrimaryUrl;
    }

    public function setHeroCtaPrimaryUrl(?string $heroCtaPrimaryUrl): static
    {
        $this->heroCtaPrimaryUrl = $heroCtaPrimaryUrl ?? '';

        return $this;
    }

    public function getHeroCtaSecondaryLabel(): string
    {
        return $this->heroCtaSecondaryLabel;
    }

    public function setHeroCtaSecondaryLabel(?string $heroCtaSecondaryLabel): static
    {
        $this->heroCtaSecondaryLabel = $heroCtaSecondaryLabel ?? '';

        return $this;
    }

    public function getHeroCtaSecondaryUrl(): string
    {
        return $this->heroCtaSecondaryUrl;
    }

    public function setHeroCtaSecondaryUrl(?string $heroCtaSecondaryUrl): static
    {
        $this->heroCtaSecondaryUrl = $heroCtaSecondaryUrl ?? '';

        return $this;
    }

    public function getStat1Value(): string { return $this->stat1Value; }
    public function setStat1Value(?string $v): static { $this->stat1Value = $v ?? ''; return $this; }
    public function getStat1Icon(): ?string { return $this->stat1Icon; }
    public function setStat1Icon(?string $v): static { $this->stat1Icon = $v !== '' ? $v : null; return $this; }
    public function getStat1Label(): string { return $this->stat1Label; }
    public function setStat1Label(?string $v): static { $this->stat1Label = $v ?? ''; return $this; }

    public function getStat2Value(): string { return $this->stat2Value; }
    public function setStat2Value(?string $v): static { $this->stat2Value = $v ?? ''; return $this; }
    public function getStat2Icon(): ?string { return $this->stat2Icon; }
    public function setStat2Icon(?string $v): static { $this->stat2Icon = $v !== '' ? $v : null; return $this; }
    public function getStat2Label(): string { return $this->stat2Label; }
    public function setStat2Label(?string $v): static { $this->stat2Label = $v ?? ''; return $this; }

    public function getStat3Value(): ?string { return $this->stat3Value; }
    public function setStat3Value(?string $v): static { $this->stat3Value = $v !== '' ? $v : null; return $this; }
    public function getStat3Icon(): ?string { return $this->stat3Icon; }
    public function setStat3Icon(?string $v): static { $this->stat3Icon = $v !== '' ? $v : null; return $this; }
    public function getStat3Label(): string { return $this->stat3Label; }
    public function setStat3Label(?string $v): static { $this->stat3Label = $v ?? ''; return $this; }

    public function getStat4Value(): ?string { return $this->stat4Value; }
    public function setStat4Value(?string $v): static { $this->stat4Value = $v !== '' ? $v : null; return $this; }
    public function getStat4Icon(): ?string { return $this->stat4Icon; }
    public function setStat4Icon(?string $v): static { $this->stat4Icon = $v !== '' ? $v : null; return $this; }
    public function getStat4Label(): string { return $this->stat4Label; }
    public function setStat4Label(?string $v): static { $this->stat4Label = $v ?? ''; return $this; }

    public function getMissionTitle(): string { return $this->missionTitle; }
    public function setMissionTitle(?string $v): static { $this->missionTitle = $v ?? ''; return $this; }
    public function getMissionLead(): string { return $this->missionLead; }
    public function setMissionLead(?string $v): static { $this->missionLead = $v ?? ''; return $this; }
    public function getMissionBody(): string { return $this->missionBody; }
    public function setMissionBody(?string $v): static { $this->missionBody = $v ?? ''; return $this; }
    public function getMissionCardTitle(): string { return $this->missionCardTitle; }
    public function setMissionCardTitle(?string $v): static { $this->missionCardTitle = $v ?? ''; return $this; }
    public function getMissionListItems(): string { return $this->missionListItems; }
    public function setMissionListItems(?string $v): static { $this->missionListItems = $v ?? ''; return $this; }

    public function getAudienceTitle(): string { return $this->audienceTitle; }
    public function setAudienceTitle(?string $v): static { $this->audienceTitle = $v ?? ''; return $this; }
    public function getAudienceLead(): string { return $this->audienceLead; }
    public function setAudienceLead(?string $v): static { $this->audienceLead = $v ?? ''; return $this; }
    public function getAudienceCard1Title(): string { return $this->audienceCard1Title; }
    public function setAudienceCard1Title(?string $v): static { $this->audienceCard1Title = $v ?? ''; return $this; }
    public function getAudienceCard1Text(): string { return $this->audienceCard1Text; }
    public function setAudienceCard1Text(?string $v): static { $this->audienceCard1Text = $v ?? ''; return $this; }
    public function getAudienceCard1Icon(): string { return $this->audienceCard1Icon; }
    public function setAudienceCard1Icon(?string $v): static { $this->audienceCard1Icon = $v ?? 'building'; return $this; }
    public function getAudienceCard2Title(): string { return $this->audienceCard2Title; }
    public function setAudienceCard2Title(?string $v): static { $this->audienceCard2Title = $v ?? ''; return $this; }
    public function getAudienceCard2Text(): string { return $this->audienceCard2Text; }
    public function setAudienceCard2Text(?string $v): static { $this->audienceCard2Text = $v ?? ''; return $this; }
    public function getAudienceCard2Icon(): string { return $this->audienceCard2Icon; }
    public function setAudienceCard2Icon(?string $v): static { $this->audienceCard2Icon = $v ?? 'people'; return $this; }

    public function getServicesTitle(): string { return $this->servicesTitle; }
    public function setServicesTitle(?string $v): static { $this->servicesTitle = $v ?? ''; return $this; }
    public function getServicesLead(): string { return $this->servicesLead; }
    public function setServicesLead(?string $v): static { $this->servicesLead = $v ?? ''; return $this; }
    public function getService1Title(): string { return $this->service1Title; }
    public function setService1Title(?string $v): static { $this->service1Title = $v ?? ''; return $this; }
    public function getService1Text(): string { return $this->service1Text; }
    public function setService1Text(?string $v): static { $this->service1Text = $v ?? ''; return $this; }
    public function getService1Icon(): string { return $this->service1Icon; }
    public function setService1Icon(?string $v): static { $this->service1Icon = $v ?? 'person-check'; return $this; }
    public function getService2Title(): string { return $this->service2Title; }
    public function setService2Title(?string $v): static { $this->service2Title = $v ?? ''; return $this; }
    public function getService2Text(): string { return $this->service2Text; }
    public function setService2Text(?string $v): static { $this->service2Text = $v ?? ''; return $this; }
    public function getService2Icon(): string { return $this->service2Icon; }
    public function setService2Icon(?string $v): static { $this->service2Icon = $v ?? 'book'; return $this; }
    public function getService3Title(): string { return $this->service3Title; }
    public function setService3Title(?string $v): static { $this->service3Title = $v ?? ''; return $this; }
    public function getService3Text(): string { return $this->service3Text; }
    public function setService3Text(?string $v): static { $this->service3Text = $v ?? ''; return $this; }
    public function getService3Icon(): string { return $this->service3Icon; }
    public function setService3Icon(?string $v): static { $this->service3Icon = $v ?? 'graph-up-arrow'; return $this; }

    public function getContactTitle(): string { return $this->contactTitle; }
    public function setContactTitle(?string $v): static { $this->contactTitle = $v ?? ''; return $this; }
    public function getContactLead(): string { return $this->contactLead; }
    public function setContactLead(?string $v): static { $this->contactLead = $v ?? ''; return $this; }
}
