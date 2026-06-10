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
            return;
        }

        $couponParams = [
            'duration' => 'once',
            'name' => sprintf(
                'Formation #%d – %s',
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

        try {
            $coupon = Coupon::create($couponParams);
            $promotionCode = PromotionCode::create([
                'promotion' => [
                    'type' => 'coupon',
                    'coupon' => $coupon->id,
                ],
                'code' => $promoCode->getCode(),
            ]);
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
                        'Ce code est valide pour la formation « %s » uniquement.',
                        $other->getFormation()?->getTitre() ?? 'autre',
                    ),
                );
            }

            return null;
        }

        if ($promoCode->getStripePromotionCodeId() === null) {
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
