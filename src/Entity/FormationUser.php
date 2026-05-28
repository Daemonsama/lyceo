<?php

namespace App\Entity;

use App\Repository\FormationUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationUserRepository::class)]
class FormationUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_achat = null;

    #[ORM\Column]
    private int $montant;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mode_paiement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $date_maj_chapitre = null;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    #[ORM\ManyToOne(inversedBy: 'formationUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Chapitre $chapitreEncours = null;

    /**
     * @param User|null $user
     * @param Formation|null $formation
     */
    public function __construct(?User $user, ?Formation $formation)
    {
        $this->user = $user;
        $this->formation = $formation;
        $this->setMontant($formation->getPrix());
        $this->date_achat = new \DateTime();
        $this->chapitreEncours = $formation->getChapitres()->first();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAchat(): ?\DateTime
    {
        return $this->date_achat;
    }

    public function setDateAchat(\DateTime $date_achat): static
    {
        $this->date_achat = $date_achat;

        return $this;
    }

    public function getMontant(): float
    {
        return $this->montant/100;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant*100;

        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setModePaiement(string $mode_paiement): static
    {
        $this->mode_paiement = $mode_paiement;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateMajChapitre(): ?\DateTime
    {
        return $this->date_maj_chapitre;
    }

    public function setDateMajChapitre(\DateTime $date_maj_chapitre): static
    {
        $this->date_maj_chapitre = $date_maj_chapitre;

        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getChapitreEncours(): ?Chapitre
    {
        return $this->chapitreEncours;
    }

    public function setChapitreEncours(?Chapitre $chapitreEncours): static
    {
        $this->chapitreEncours = $chapitreEncours;

        return $this;
    }
}
