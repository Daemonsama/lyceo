<?php

namespace App\Service;

use App\Entity\Formation;
use App\Entity\FormationUser;
use App\Entity\User;
use App\Exception\InvalidPromoCodeException;
use App\Repository\FormationRepository;
use App\Repository\FormationUserRepository;
use App\Repository\UserRepository;
use Stripe\Checkout\Session;
use Stripe\PromotionCode;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

final class StripePaymentService
{
    public function __construct(
        private readonly string $stripeSecretKey,
        private readonly string $stripeWebhookSecret,
        private readonly FormationRepository $formationRepository,
        private readonly FormationUserRepository $formationUserRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function createCheckoutSession(
        Formation $formation,
        User $user,
        string $successUrl,
        string $cancelUrl,
        ?string $promoCode = null,
    ): Session {
        $this->configureApiKey();

        $params = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $formation->getTitre(),
                    ],
                    'unit_amount' => (int) round($formation->getPrix() * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'formation_id' => (string) $formation->getId(),
                'user_id' => (string) $user->getId(),
            ],
            'customer_email' => $user->getEmail(),
        ];

        $promoCode = $promoCode !== null ? trim($promoCode) : '';
        if ($promoCode !== '') {
            $promotionCodeId = $this->findPromotionCodeId($promoCode);
            if ($promotionCodeId === null) {
                throw new InvalidPromoCodeException($promoCode);
            }
            $params['discounts'] = [['promotion_code' => $promotionCodeId]];
            $params['metadata']['promo_code'] = $promoCode;
        } else {
            $params['allow_promotion_codes'] = true;
        }

        return Session::create($params);
    }

    public function findPromotionCodeId(string $code): ?string
    {
        $this->configureApiKey();

        $promotionCodes = PromotionCode::all([
            'code' => trim($code),
            'active' => true,
            'limit' => 1,
        ]);

        if ($promotionCodes->data === []) {
            return null;
        }

        return $promotionCodes->data[0]->id;
    }

    public function retrieveCheckoutSession(string $sessionId): Session
    {
        $this->configureApiKey();

        return Session::retrieve($sessionId);
    }

    /**
     * Enregistre l'achat si la session Stripe est payée et correspond à l'utilisateur / la formation.
     */
    public function fulfillCheckoutSession(Session $session, User $user, Formation $formation): bool
    {
        if ($session->payment_status !== 'paid') {
            return false;
        }

        $formationId = $session->metadata['formation_id'] ?? null;
        $userId = $session->metadata['user_id'] ?? null;

        if ((string) $formation->getId() !== (string) $formationId
            || (string) $user->getId() !== (string) $userId) {
            return false;
        }

        if ($user->hasFormation($formation)) {
            return true;
        }

        $formationUser = new FormationUser($user, $formation);
        $formationUser->setModePaiement('stripe');
        $formationUser->setStatut('paye');

        if ($session->amount_total !== null) {
            $formationUser->setMontant($session->amount_total / 100);
        }

        $this->formationUserRepository->save($formationUser);

        return true;
    }

    public function parseWebhookEvent(string $payload, ?string $signature): ?Event
    {
        if ($this->stripeWebhookSecret === '') {
            return null;
        }

        if ($signature === null || $signature === '') {
            return null;
        }

        try {
            return Webhook::constructEvent($payload, $signature, $this->stripeWebhookSecret);
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return null;
        }
    }

    public function handleWebhookEvent(Event $event): void
    {
        if ($event->type !== 'checkout.session.completed') {
            return;
        }

        /** @var Session $session */
        $session = $event->data->object;

        if ($session->payment_status !== 'paid') {
            return;
        }

        $formationId = $session->metadata['formation_id'] ?? null;
        $userId = $session->metadata['user_id'] ?? null;

        if ($formationId === null || $userId === null) {
            return;
        }

        $formation = $this->formationRepository->find($formationId);
        $user = $this->userRepository->find($userId);

        if ($formation === null || $user === null) {
            return;
        }

        $this->fulfillCheckoutSession($session, $user, $formation);
    }

    private function configureApiKey(): void
    {
        Stripe::setApiKey($this->stripeSecretKey);
    }
}
