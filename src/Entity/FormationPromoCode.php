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
#[UniqueEntity(fields: ['formation', 'code'], message: 'Ce code promo existe déjà pour cette formation.')]
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
