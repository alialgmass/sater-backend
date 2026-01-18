<?php

namespace App\Gateways\Payment;

use App\Interfaces\Payment\PaymentGatewayInterface;
use App\DTOs\Payment\PaymentInitiationDTO;
use App\DTOs\Payment\PaymentVerificationDTO;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function initiatePayment(PaymentInitiationDTO $dto): array
    {
        try {
            if ($dto->method->isOnline()) {
                // Create a checkout session for online payments
                $session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => strtolower($dto->currency),
                            'product_data' => [
                                'name' => $dto->description ?: 'Order Payment',
                            ],
                            'unit_amount' => (int) round($dto->amount * 100), // Convert to cents
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => $dto->returnUrl . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $dto->cancelUrl,
                    'client_reference_id' => $dto->referenceId ?? $dto->vendorOrderId,
                    'customer_email' => $dto->customerEmail,
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $session->id,
                    'reference_id' => $session->client_reference_id,
                    'payment_url' => $session->url,
                    'redirect_url' => $session->url,
                    'status' => 'pending',
                    'message' => 'Payment session created successfully',
                ];
            } else {
                // For COD, we don't need to call Stripe
                return [
                    'success' => true,
                    'transaction_id' => 'STRIPE_COD_' . uniqid(),
                    'reference_id' => 'STRIPE_COD_REF_' . uniqid(),
                    'status' => 'pending',
                    'message' => 'COD payment initiated',
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment initiation failed',
            ];
        }
    }

    public function verifyPayment(PaymentVerificationDTO $dto): array
    {
        try {
            $session = Session::retrieve($dto->transactionId);
            
            return [
                'success' => true,
                'status' => $session->payment_status,
                'transaction_id' => $session->id,
                'reference_id' => $session->client_reference_id,
                'payment_intent' => $session->payment_intent,
                'amount_total' => $session->amount_total,
                'currency' => $session->currency,
                'customer_email' => $session->customer_details?->email,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Payment verification failed',
            ];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    
                    return [
                        'status' => 'success',
                        'transaction_id' => $session->id,
                        'reference_id' => $session->client_reference_id,
                        'payment_intent' => $session->payment_intent,
                        'amount' => $session->amount_total / 100, // Convert from cents
                        'currency' => $session->currency,
                        'customer_email' => $session->customer_details?->email,
                    ];
                    
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    
                    return [
                        'status' => 'success',
                        'transaction_id' => $paymentIntent->id,
                        'reference_id' => $paymentIntent->id,
                        'amount' => $paymentIntent->amount / 100, // Convert from cents
                        'currency' => $paymentIntent->currency,
                        'customer_email' => $paymentIntent->charges->data[0]?->billing_details?->email ?? null,
                    ];
                    
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    
                    return [
                        'status' => 'failed',
                        'transaction_id' => $paymentIntent->id,
                        'reference_id' => $paymentIntent->id,
                        'error_message' => $paymentIntent->last_payment_error?->message ?? 'Payment failed',
                    ];
                    
                default:
                    return [
                        'status' => 'ignored',
                        'message' => 'Unhandled event type: ' . $event->type,
                    ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ];
        }
    }

    public function refund(string $transactionId, float $amount, ?string $reason = null): array
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $transactionId,
                'amount' => (int) round($amount * 100), // Convert to cents
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100, // Convert from cents
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Refund failed',
            ];
        }
    }

    public function supportsMethod(string $method): bool
    {
        return in_array($method, [
            'credit_card',
            'debit_card',
            'card'
        ]);
    }

    public function getConfig(): array
    {
        return [
            'name' => 'Stripe',
            'supports_cards' => true,
            'supports_wallets' => false,
            'supports_cod' => false,
            'currencies' => ['usd', 'eur', 'gbp', 'cad', 'aud', 'jpy', 'sgd', 'myr'],
        ];
    }

    public function validateSignature(Request $request): bool
    {
        $signatureHeader = $request->header('Stripe-Signature');
        $payload = $request->getContent();
        $webhookSecret = config('services.stripe.webhook_secret');

        if (!$signatureHeader || !$webhookSecret) {
            return false;
        }

        try {
            Webhook::constructEvent($payload, $signatureHeader, $webhookSecret);
            return true;
        } catch (\Exception $e) {
            \Log::error('Stripe webhook signature validation failed', [
                'error' => $e->getMessage(),
                'signature_header' => $signatureHeader,
            ]);
            return false;
        }
    }
}