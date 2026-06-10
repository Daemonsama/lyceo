<?php

namespace App\Controller;

use App\Service\StripePaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StripeWebhookController extends AbstractController
{
    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function __invoke(Request $request, StripePaymentService $stripePayment): Response
    {
        $event = $stripePayment->parseWebhookEvent(
            $request->getContent(),
            $request->headers->get('Stripe-Signature'),
        );

        if ($event === null) {
            return new Response('Invalid payload or signature', Response::HTTP_BAD_REQUEST);
        }

        $stripePayment->handleWebhookEvent($event);

        return new Response('OK', Response::HTTP_OK);
    }
}
