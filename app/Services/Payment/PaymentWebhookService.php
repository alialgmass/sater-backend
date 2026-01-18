<?php

namespace App\Services\Payment;

use App\Models\Payment\Payment;
use App\Enums\Payment\PaymentStatusEnum;
use App\Enums\Payment\GatewayEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentWebhookService
{
    public function __construct(
        protected PaymentGatewayFactory $gatewayFactory,
        protected PaymentService $paymentService
    ) {}

    /**
     * Process webhook from payment gateway
     */
    public function process(string $gateway, array $payload): array
    {
        try {
            // Validate gateway
            $gatewayEnum = GatewayEnum::tryFrom($gateway);
            if (!$gatewayEnum) {
                throw new \InvalidArgumentException("Invalid gateway: {$gateway}");
            }

            // Get the appropriate gateway handler
            $gatewayHandler = $this->gatewayFactory->getGateway($gatewayEnum);

            // Validate the webhook signature
            $request = Request::createFrom(request()->instance());
            if (!$gatewayHandler->validateSignature($request)) {
                Log::warning('Webhook signature validation failed', [
                    'gateway' => $gateway,
                    'payload' => $payload,
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Invalid signature',
                ];
            }

            // Process the webhook
            $result = $gatewayHandler->handleWebhook($request);

            // Find the payment by transaction ID
            $payment = Payment::where('transaction_id', $result['transaction_id'])->first();

            if (!$payment) {
                Log::warning('Payment not found for webhook', [
                    'transaction_id' => $result['transaction_id'],
                    'gateway' => $gateway,
                    'payload' => $payload,
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Payment not found',
                ];
            }

            // Update payment based on webhook result
            if ($result['status'] === 'success') {
                $this->paymentService->processPaymentSuccess($payment, $result);
            } else {
                $this->paymentService->processPaymentFailure(
                    $payment, 
                    $result['error_message'] ?? 'Webhook processing failed', 
                    $result
                );
            }

            return [
                'success' => true,
                'message' => 'Webhook processed successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'Webhook processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle idempotent webhook processing
     */
    public function processIdempotent(string $gateway, array $payload): array
    {
        // Create a unique identifier for this webhook event
        $eventId = $this->generateEventId($gateway, $payload);
        
        // Check if this event has already been processed
        $cacheKey = "payment_webhook_processed_{$eventId}";
        $alreadyProcessed = cache()->has($cacheKey);
        
        if ($alreadyProcessed) {
            Log::info('Duplicate webhook received, skipping', [
                'event_id' => $eventId,
                'gateway' => $gateway,
            ]);
            
            return [
                'success' => true,
                'message' => 'Webhook already processed',
            ];
        }
        
        // Process the webhook
        $result = $this->process($gateway, $payload);
        
        // Cache the event ID to prevent duplicate processing
        cache()->put($cacheKey, true, now()->addHours(24));
        
        return $result;
    }
    
    /**
     * Generate unique event ID for idempotency
     */
    protected function generateEventId(string $gateway, array $payload): string
    {
        // Use gateway-specific event ID if available
        if (isset($payload['event_id'])) {
            return "{$gateway}_{$payload['event_id']}";
        }
        
        // Fallback to hashing the payload
        return $gateway . '_' . md5(json_encode($payload));
    }
}