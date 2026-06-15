<?php

namespace App\Service;

use App\Entity\Formation;
use App\Entity\FormationUser;
use App\Entity\User;
use App\Exception\InvalidPromoCodeException;
use App\Exception\StripePaymentException;
use App\Repository\FormationRepository;
use App\Repository\FormationUserRepository;
use App\Repository\UserRepository;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
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
        private readonly StripePromoCodeSyncService $promoCodeSyncService,
    ) {
    }

    public function createCheckoutSession(
        Formation $formation,
        User $user,
        string $successUrl,
        string $cancelUrl,
        ?string $promoCode = null,
    ): Session {
        $this->assertStripeConfigured();

        $unitAmount = (int) round($formation->getPrix() * 100);
        if ($unitAmount < 50) {
            throw new StripePaymentException(
                'Le montant doit être d\'au moins 0,50 € pour un paiement Stripe.'
            );
        }

        $productName = $formation->getTitre();

        $params = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $productName,
                    ],
                    'unit_amount' => $unitAmount,
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
            try {
                $promotionCodeId = $this->promoCodeSyncService->resolvePromotionCodeId($formation, $promoCode);
            } catch (InvalidPromoCodeException $e) {
                throw $e;
            }

            if ($promotionCodeId === null) {
                throw new InvalidPromoCodeException($promoCode);
            }
            $params['discounts'] = [['promotion_code' => $promotionCodeId]];
            $params['metadata']['promo_code'] = strtoupper($promoCode);
        }

        try {
            return Session::create($params);
        } catch (ApiErrorException $e) {
            throw new StripePaymentException($this->formatApiErrorMessage($e), 0, $e);
        }
    }

    public function retrieveCheckoutSession(string $sessionId, bool $withPaymentDetails = false): Session
    {
        $this->assertStripeConfigured();

        $params = $withPaymentDetails
            ? ['expand' => ['payment_intent.payment_method']]
            : [];

        try {
            return Session::retrieve($sessionId, $params);
        } catch (ApiErrorException $e) {
            throw new StripePaymentException($this->formatApiErrorMessage($e), 0, $e);
        }
    }

    /**
     * @return array{
     *     formation: array{id: int|null, titre: string|null},
     *     customerName: string,
     *     referenceId: string,
     *     transactionId: string,
     *     invoiceId: string,
     *     paymentMethod: string,
     *     cardBrand: string|null,
     *     cardLast4: string|null,
     *     paidAtLabel: string,
     *     amount: float,
     *     currency: string,
     *     accessLabel: string
     * }
     */
    public function buildReceiptData(Session $session, Formation $formation, User $user): array
    {
        $cardBrand = null;
        $cardLast4 = null;
        $paymentIntentId = null;

        if (is_object($session->payment_intent)) {
            $paymentIntentId = $session->payment_intent->id ?? null;
            $paymentMethod = $session->payment_intent->payment_method ?? null;
            if (is_object($paymentMethod) && isset($paymentMethod->card)) {
                $cardBrand = $paymentMethod->card->brand ?? null;
                $cardLast4 = $paymentMethod->card->last4 ?? null;
            }
        } elseif (is_string($session->payment_intent) && $session->payment_intent !== '') {
            $paymentIntentId = $session->payment_intent;
        }

        $paidAt = \DateTimeImmutable::createFromFormat('U', (string) ($session->created ?? time()));
        if ($paidAt === false) {
            $paidAt = new \DateTimeImmutable();
        }

        $customerName = trim(sprintf('%s %s', $user->getPrenom() ?? '', $user->getNom() ?? ''));
        if ($customerName === '') {
            $customerName = (string) $user->getEmail();
        }

        return [
            'formation' => [
                'id' => $formation->getId(),
                'titre' => $formation->getTitre(),
            ],
            'customerName' => $customerName,
            'referenceId' => $this->formatGroupedId($session->id),
            'transactionId' => $paymentIntentId !== null ? $this->formatShortId($paymentIntentId) : '—',
            'invoiceId' => $this->formatShortId((string) ($session->invoice ?? $session->id)),
            'paymentMethod' => 'Carte bancaire',
            'cardBrand' => $cardBrand,
            'cardLast4' => $cardLast4,
            'paidAtLabel' => $this->formatFrenchDateTime($paidAt),
            'amount' => ($session->amount_total ?? 0) / 100,
            'currency' => strtoupper((string) ($session->currency ?? 'eur')),
            'accessLabel' => 'Accès illimité — valable sans date d\'expiration',
        ];
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

    private function assertStripeConfigured(): void
    {
        $key = trim($this->stripeSecretKey);

        if ($key === '' || str_contains($key, 'change_me')) {
            throw new StripePaymentException(
                'Stripe n\'est pas configuré : ajoutez votre clé secrète de test (sk_test_...) dans le fichier .env.local.'
            );
        }

        if (!str_starts_with($key, 'sk_test_') && !str_starts_with($key, 'sk_live_')) {
            throw new StripePaymentException(
                'STRIPE_SECRET_KEY est invalide : utilisez la clé secrète (sk_test_...), pas la clé publique (pk_test_...).'
            );
        }

        Stripe::setApiKey($key);
    }

    private function formatFrenchDateTime(\DateTimeInterface $date): string
    {
        $days = [
            1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi',
            5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche',
        ];
        $months = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];

        $dayName = $days[(int) $date->format('N')] ?? $date->format('l');
        $monthName = $months[(int) $date->format('n')] ?? $date->format('F');

        return sprintf(
            '%s, %s %d %s %s',
            $date->format('H:i'),
            $dayName,
            (int) $date->format('j'),
            $monthName,
            $date->format('Y'),
        );
    }

    private function formatGroupedId(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '') {
            return $value;
        }

        return trim(chunk_split($digits, 4, ' '));
    }

    private function formatShortId(string $value): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $value) ?? $value;
        if (strlen($normalized) <= 12) {
            return strtoupper($normalized);
        }

        return strtoupper(substr($normalized, -12));
    }

    private function formatApiErrorMessage(ApiErrorException $e): string
    {
        $stripeMessage = $e->getMessage();

        return match ($e->getStripeCode()) {
            'invalid_api_key' => 'Clé API Stripe invalide. Vérifiez STRIPE_SECRET_KEY dans .env.local (mode test : sk_test_...).',
            'amount_too_small' => 'Le montant est trop faible pour Stripe (minimum 0,50 €).',
            default => 'Erreur Stripe : '.$stripeMessage,
        };
    }
}
