<?php

namespace App\Entity;

use App\Repository\HomePromoBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HomePromoBlockRepository::class)]
class HomePromoBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $sectionTitle = 'À découvrir';

    #[ORM\Column(type: Types::TEXT)]
    private string $sectionLead = '';

    /** Lien (YouTube, Vimeo, ou URL directe .mp4 / .webm, etc.) */
    #[ORM\Column(type: Types::TEXT)]
    private string $videoUrl = '';

    /** Fichier dans public/uploads/promo/ — prioritaire sur l’URL. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoFilename = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSectionTitle(): string
    {
        return $this->sectionTitle;
    }

    public function setSectionTitle(?string $sectionTitle): static
    {
        $this->sectionTitle = $sectionTitle ?? '';

        return $this;
    }

    public function getSectionLead(): string
    {
        return $this->sectionLead;
    }

    public function setSectionLead(?string $sectionLead): static
    {
        $this->sectionLead = $sectionLead ?? '';

        return $this;
    }

    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl ?? '';

        return $this;
    }

    public function getVideoFilename(): ?string
    {
        return $this->videoFilename;
    }

    public function setVideoFilename(?string $videoFilename): static
    {
        $this->videoFilename = $videoFilename;

        return $this;
    }

    public function hasUploadedVideo(): bool
    {
        return $this->videoFilename !== null && $this->videoFilename !== '';
    }

    public function hasAnyVideo(): bool
    {
        if ($this->hasUploadedVideo()) {
            return true;
        }

        return trim($this->videoUrl) !== '';
    }
}
