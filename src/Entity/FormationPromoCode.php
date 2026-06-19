<?php

namespace App\Entity;

use App\Repository\FormationPromoCodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: FormationPromoCodeRepository::class)]
#[ORM\Table(name: 'formation_promo_code')]
#[ORM\UniqueConstraint(name: 'uniq_formation_promo_code', fields: ['formation', 'code'])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['formation', 'code'], message: 'Ce code promo existe déjà pour ce module.')]
class FormationPromoCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'promoCodes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Formation $formation = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9_-]+$/',
        message: 'Le code ne peut contenir que des lettres, chiffres, tirets et underscores.',
    )]
    private ?string $code = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 1, max: 100)]
    private ?int $discountPercent = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    private ?int $discountAmount = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePromotionCodeId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeCouponId = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'La durée de validité doit être d\'au moins 1 jour.')]
    private ?int $validityDays = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[Assert\Callback]
    public function validateDiscount(ExecutionContextInterface $context): void
    {
        $hasPercent = $this->discountPercent !== null;
        $hasAmount = $this->discountAmount !== null;

        if (!$hasPercent && !$hasAmount) {
            $context->buildViolation('Indiquez une réduction en pourcentage ou en montant fixe.')
                ->atPath('discountPercent')
                ->addViolation();
        }

        if ($hasPercent && $hasAmount) {
            $context->buildViolation('Choisissez soit un pourcentage, soit un montant fixe, pas les deux.')
                ->atPath('discountPercent')
                ->addViolation();
        }
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper(trim($code));

        return $this;
    }

    public function getDiscountPercent(): ?int
    {
        return $this->discountPercent;
    }

    public function setDiscountPercent(?int $discountPercent): static
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    public function getDiscountAmount(): ?float
    {
        return $this->discountAmount === null ? null : $this->discountAmount / 100;
    }

    public function setDiscountAmount(?float $discountAmount): static
    {
        $this->discountAmount = $discountAmount === null ? null : (int) round($discountAmount * 100);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getStripePromotionCodeId(): ?string
    {
        return $this->stripePromotionCodeId;
    }

    public function setStripePromotionCodeId(?string $stripePromotionCodeId): static
    {
        $this->stripePromotionCodeId = $stripePromotionCodeId;

        return $this;
    }

    public function getStripeCouponId(): ?string
    {
        return $this->stripeCouponId;
    }

    public function setStripeCouponId(?string $stripeCouponId): static
    {
        $this->stripeCouponId = $stripeCouponId;

        return $this;
    }

    public function getValidityDays(): ?int
    {
        return $this->validityDays;
    }

    public function setValidityDays(?int $validityDays): static
    {
        $this->validityDays = $validityDays;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        if ($this->validityDays === null || $this->createdAt === null) {
            return null;
        }

        return $this->createdAt->modify(sprintf('+%d days', $this->validityDays));
    }

    public function isExpired(): bool
    {
        $expiresAt = $this->getExpiresAt();

        return $expiresAt !== null && $expiresAt <= new \DateTimeImmutable();
    }

    public function getValidityLabel(): string
    {
        if ($this->validityDays === null) {
            return 'Sans limite';
        }

        $expiresAt = $this->getExpiresAt();
        if ($expiresAt === null) {
            return sprintf('%d jour(s)', $this->validityDays);
        }

        if ($this->isExpired()) {
            return sprintf('Expiré le %s', $expiresAt->format('d/m/Y'));
        }

        return sprintf('Expire le %s', $expiresAt->format('d/m/Y'));
    }

    public function getDiscountLabel(): string
    {
        if ($this->discountPercent !== null) {
            return sprintf('-%d %%', $this->discountPercent);
        }

        if ($this->discountAmount !== null) {
            return sprintf('-%.2f €', $this->discountAmount / 100);
        }

        return '';
    }
}
