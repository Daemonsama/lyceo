<?php

namespace App\Service;

use App\Entity\Formation;
use App\Entity\FormationPromoCode;
use App\Exception\InvalidPromoCodeException;
use App\Exception\StripePaymentException;
use App\Repository\FormationPromoCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Coupon;
use Stripe\Exception\ApiErrorException;
use Stripe\PromotionCode;
use Stripe\Stripe;

final class StripePromoCodeSyncService
{
    public function __construct(
        private readonly string $stripeSecretKey,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormationPromoCodeRepository $promoCodeRepository,
    ) {
    }

    /**
     * @return list<string> Messages d'erreur par code
     */
    public function syncFormation(Formation $formation): array
    {
        $errors = [];

        foreach ($formation->getPromoCodes() as $promoCode) {
            if (!$promoCode->isActive()) {
                continue;
            }

            try {
                $this->syncPromoCode($promoCode);
            } catch (StripePaymentException $e) {
                $errors[] = sprintf('%s : %s', $promoCode->getCode(), $e->getMessage());
            }
        }

        $this->entityManager->flush();

        return $errors;
    }

    public function syncPromoCode(FormationPromoCode $promoCode): void
    {
        $this->assertStripeConfigured();

        if ($promoCode->getStripePromotionCodeId() !== null) {
            if ($this->stripePromotionMatches($promoCode)) {
                return;
            }

            $this->deactivateStripePromotion($promoCode);
            $promoCode->setStripePromotionCodeId(null);
            $promoCode->setStripeCouponId(null);
        }

        $couponParams = [
            'duration' => 'once',
            'name' => sprintf(
                'Module #%d – %s',
                $promoCode->getFormation()?->getId(),
                $promoCode->getCode(),
            ),
        ];

        if ($promoCode->getDiscountPercent() !== null) {
            $couponParams['percent_off'] = $promoCode->getDiscountPercent();
        } else {
            $couponParams['amount_off'] = (int) round($promoCode->getDiscountAmount() * 100);
            $couponParams['currency'] = 'eur';
        }

        $expiresAt = $promoCode->getExpiresAt();
        if ($expiresAt !== null) {
            $couponParams['redeem_by'] = $expiresAt->getTimestamp();
        }

        try {
            $coupon = Coupon::create($couponParams);
            $promotionCodeParams = [
                'promotion' => [
                    'type' => 'coupon',
                    'coupon' => $coupon->id,
                ],
                'code' => $promoCode->getCode(),
            ];

            if ($expiresAt !== null) {
                $promotionCodeParams['expires_at'] = $expiresAt->getTimestamp();
            }

            $promotionCode = PromotionCode::create($promotionCodeParams);
        } catch (ApiErrorException $e) {
            if ($this->linkExistingStripePromotionCode($promoCode)) {
                return;
            }

            throw new StripePaymentException($this->formatApiErrorMessage($e));
        }

        $promoCode->setStripeCouponId($coupon->id);
        $promoCode->setStripePromotionCodeId($promotionCode->id);
    }

    public function resolvePromotionCodeId(Formation $formation, string $code): ?string
    {
        $normalizedCode = strtoupper(trim($code));
        $promoCode = $this->promoCodeRepository->findActiveByFormationAndCode($formation, $normalizedCode);

        if ($promoCode === null) {
            $other = $this->promoCodeRepository->findOneBy(['code' => $normalizedCode]);
            if ($other !== null && $other->getFormation()?->getId() !== $formation->getId()) {
                throw new InvalidPromoCodeException(
                    $normalizedCode,
                    sprintf(
                        'Ce code est valide pour le module « %s » uniquement.',
                        $other->getFormation()?->getTitre() ?? 'autre',
                    ),
                );
            }

            $expired = $this->promoCodeRepository->findExpiredByFormationAndCode($formation, $normalizedCode);
            if ($expired !== null) {
                throw new InvalidPromoCodeException(
                    $normalizedCode,
                    sprintf('Ce code promo a expiré le %s.', $expired->getExpiresAt()?->format('d/m/Y') ?? ''),
                );
            }

            return null;
        }

        if ($promoCode->isExpired()) {
            throw new InvalidPromoCodeException(
                $normalizedCode,
                sprintf('Ce code promo a expiré le %s.', $promoCode->getExpiresAt()?->format('d/m/Y') ?? ''),
            );
        }

        if ($promoCode->getStripePromotionCodeId() === null || !$this->stripePromotionMatches($promoCode)) {
            try {
                $this->syncPromoCode($promoCode);
                $this->entityManager->flush();
            } catch (StripePaymentException $e) {
                throw new InvalidPromoCodeException(
                    $normalizedCode,
                    'Synchronisation Stripe impossible : '.$e->getMessage(),
                );
            }
        }

        return $promoCode->getStripePromotionCodeId();
    }

    private function stripePromotionMatches(FormationPromoCode $promoCode): bool
    {
        $promotionCodeId = $promoCode->getStripePromotionCodeId();
        if ($promotionCodeId === null) {
            return false;
        }

        try {
            $promotionCode = PromotionCode::retrieve($promotionCodeId, [
                'expand' => ['promotion.coupon'],
            ]);
        } catch (ApiErrorException) {
            return false;
        }

        if (!$promotionCode->active) {
            return false;
        }

        return $this->promotionCodeMatchesLocal($promoCode, $promotionCode);
    }

    private function promotionCodeMatchesLocal(FormationPromoCode $promoCode, PromotionCode $promotionCode): bool
    {
        $coupon = $promotionCode->promotion->coupon ?? null;
        if (is_string($coupon)) {
            try {
                $coupon = Coupon::retrieve($coupon);
            } catch (ApiErrorException) {
                return false;
            }
        }

        if (!$coupon instanceof Coupon) {
            return false;
        }

        if ($promoCode->getDiscountPercent() !== null) {
            if ((int) $coupon->percent_off !== $promoCode->getDiscountPercent()) {
                return false;
            }
        } elseif ($promoCode->getDiscountAmount() !== null) {
            $expectedAmount = (int) round($promoCode->getDiscountAmount() * 100);
            if ((int) $coupon->amount_off !== $expectedAmount) {
                return false;
            }
        } else {
            return false;
        }

        $expectedExpiresAt = $promoCode->getExpiresAt()?->getTimestamp();
        $stripeExpiresAt = isset($promotionCode->expires_at) ? (int) $promotionCode->expires_at : null;
        $stripeRedeemBy = isset($coupon->redeem_by) ? (int) $coupon->redeem_by : null;

        if ($expectedExpiresAt !== null) {
            return $stripeExpiresAt === $expectedExpiresAt || $stripeRedeemBy === $expectedExpiresAt;
        }

        return $stripeExpiresAt === null && $stripeRedeemBy === null;
    }

    private function deactivateStripePromotion(FormationPromoCode $promoCode): void
    {
        $promotionCodeId = $promoCode->getStripePromotionCodeId();
        if ($promotionCodeId === null) {
            return;
        }

        try {
            PromotionCode::update($promotionCodeId, ['active' => false]);
        } catch (ApiErrorException) {
            // Ancien code déjà supprimé ou inactif côté Stripe.
        }
    }

    private function linkExistingStripePromotionCode(FormationPromoCode $promoCode): bool
    {
        try {
            $existing = PromotionCode::all([
                'code' => $promoCode->getCode(),
                'active' => true,
                'limit' => 1,
            ]);
        } catch (ApiErrorException) {
            return false;
        }

        if ($existing->data === []) {
            return false;
        }

        $promotionCode = $existing->data[0];
        if (!$this->promotionCodeMatchesLocal($promoCode, $promotionCode)) {
            return false;
        }

        $promoCode->setStripePromotionCodeId($promotionCode->id);

        if (isset($promotionCode->promotion->coupon)) {
            $couponId = is_string($promotionCode->promotion->coupon)
                ? $promotionCode->promotion->coupon
                : $promotionCode->promotion->coupon->id;
            $promoCode->setStripeCouponId($couponId);
        }

        return true;
    }

    private function assertStripeConfigured(): void
    {
        $key = trim($this->stripeSecretKey);

        if ($key === '' || str_contains($key, 'change_me')) {
            throw new StripePaymentException(
                'Stripe n\'est pas configuré : ajoutez STRIPE_SECRET_KEY dans .env.local.'
            );
        }

        Stripe::setApiKey($key);
    }

    private function formatApiErrorMessage(ApiErrorException $e): string
    {
        return match ($e->getStripeCode()) {
            'resource_already_exists' => 'Ce code existe déjà dans Stripe.',
            default => $e->getMessage(),
        };
    }
}
