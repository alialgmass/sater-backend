<?php

namespace Modules\Payment\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Payment\Actions\InitiatePaymentAction;
use Modules\Payment\Actions\VerifyPaymentAction;
use Modules\Payment\DTOs\PaymentInitiationDTO;
use Modules\Payment\Services\PaymentService;
use Illuminate\Support\Facades\Validator;

class PaymentController extends ApiController
{
    public function __construct(
        protected InitiatePaymentAction $initiatePaymentAction,
        protected VerifyPaymentAction $verifyPaymentAction,
        protected PaymentService $paymentService
    ) {
        parent::__construct();
    }

    /**
     * Initiate a payment
     */
    public function initiate(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer|exists:users,id',
                'vendor_order_id' => 'required|integer|exists:vendor_orders,id',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|max:3',
                'method' => 'required|string|in:cod,credit_card,debit_card,wallet,bank_transfer,mobile_money',
                'gateway' => 'nullable|string|in:stripe,paymob,fawry,stc_pay,hyper_pay,local_bank',
                'customer_email' => 'nullable|email',
                'customer_phone' => 'nullable|string',
                'customer_name' => 'nullable|string',
                'description' => 'nullable|string',
                'items' => 'nullable|array',
                'metadata' => 'nullable|array',
                'return_url' => 'nullable|url',
                'cancel_url' => 'nullable|url',
                'callback_url' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return $this->apiMessage('Validation failed')
                    ->apiBody(['errors' => $validator->errors()])
                    ->apiCode(422)
                    ->apiResponse();
            }

            // Create DTO from request
            $dto = PaymentInitiationDTO::fromArray($request->all());

            // Execute the action
            $result = $this->initiatePaymentAction->execute($dto);

            return $this->apiBody($result)->apiResponse();
        } catch (\Exception $e) {
            return $this->apiMessage('Payment initiation failed')
                ->apiBody(['error' => $e->getMessage()])
                ->apiCode(500)
                ->apiResponse();
        }
    }

    /**
     * Verify payment status
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string',
                'reference_id' => 'required|string',
                'gateway' => 'required|string|in:stripe,paymob,fawry,stc_pay,hyper_pay,local_bank',
                'payment_id' => 'nullable|integer|exists:payments,id',
                'additional_data' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->apiMessage('Validation failed')
                    ->apiBody(['errors' => $validator->errors()])
                    ->apiCode(422)
                    ->apiResponse();
            }

            // Create DTO from request
            $dto = \Modules\Payment\DTOs\PaymentVerificationDTO::fromArray($request->all());

            // Execute the action
            $result = $this->verifyPaymentAction->execute($dto);

            return $this->apiBody($result)->apiResponse();
        } catch (\Exception $e) {
            return $this->apiMessage('Payment verification failed')
                ->apiBody(['error' => $e->getMessage()])
                ->apiCode(500)
                ->apiResponse();
        }
    }

    /**
     * Get payment status by order number
     */
    public function getStatusByOrderNumber(string $orderNumber): JsonResponse
    {
        try {
            $result = $this->paymentService->getPaymentStatusByOrderNumber($orderNumber);

            if (!$result['success']) {
                $this->apiCode(404);
            }

            return $this->apiBody($result)->apiResponse();
        } catch (\Exception $e) {
            return $this->apiMessage('Failed to get payment status')
                ->apiBody(['error' => $e->getMessage()])
                ->apiCode(500)
                ->apiResponse();
        }
    }

    /**
     * Handle payment webhook
     */
    public function webhook(Request $request, string $gateway): JsonResponse
    {
        try {
            // Process the webhook
            $result = $this->paymentService->handleWebhook($gateway, $request->all());

            if (!$result['success']) {
                $this->apiCode(400);
            }

            return $this->apiBody($result)->apiResponse();
        } catch (\Exception $e) {
            \Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
                'payload' => $request->all(),
            ]);

            return $this->apiMessage('Webhook processing failed')
                ->apiCode(500)
                ->apiResponse();
        }
    }

    /**
     * Handle payment success callback
     */
    public function successCallback(Request $request): JsonResponse
    {
        // This endpoint handles successful payment redirects
        $sessionId = $request->get('session_id');
        
        return $this->apiMessage('Payment successful')
            ->apiBody(['session_id' => $sessionId])
            ->apiResponse();
    }

    /**
     * Handle payment cancellation
     */
    public function cancelCallback(Request $request): JsonResponse
    {
        // This endpoint handles cancelled payments
        return $this->apiMessage('Payment was cancelled')
            ->apiBody(['success' => false])
            ->apiResponse();
    }
}