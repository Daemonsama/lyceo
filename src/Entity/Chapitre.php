<?php

namespace App\Entity;

use App\Repository\ChapitreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChapitreRepository::class)]
class Chapitre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chapitres')]
    #[ORM\JoinColumn(nullable: false)]
    private Formation $formation;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $media = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $mediaUrl = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: false)]
    private int $ordre;

    #[ORM\OneToOne(mappedBy: 'chapitre', cascade: ['persist', 'remove'])]
    private ?Quiz $quiz = null;

    public function __construct(Formation $formation)
    {
        $this->formation = $formation;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): static
    {
        if ($contenu === null || trim(strip_tags($contenu)) === '') {
            $this->contenu = null;
        } else {
            $this->contenu = $contenu;
        }

        return $this;
    }

    public function hasTexteContenu(): bool
    {
        return $this->contenu !== null && trim(strip_tags($this->contenu)) !== '';
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl !== null && $mediaUrl !== '' ? $mediaUrl : null;

        return $this;
    }

    public function hasMedia(): bool
    {
        return ($this->media !== null && $this->media !== '')
            || ($this->mediaUrl !== null && $this->mediaUrl !== '');
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }
}
