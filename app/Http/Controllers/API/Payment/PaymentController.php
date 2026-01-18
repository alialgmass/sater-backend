<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Actions\Payment\InitiatePaymentAction;
use App\Actions\Payment\VerifyPaymentAction;
use App\DTOs\Payment\PaymentInitiationDTO;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function __construct(
        protected InitiatePaymentAction $initiatePaymentAction,
        protected VerifyPaymentAction $verifyPaymentAction,
        protected PaymentService $paymentService
    ) {}

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
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Create DTO from request
            $dto = PaymentInitiationDTO::fromArray($request->all());

            // Execute the action
            $result = $this->initiatePaymentAction->execute($dto);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $e->getMessage(),
            ], 500);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Create DTO from request
            $dto = PaymentVerificationDTO::fromArray($request->all());

            // Execute the action
            $result = $this->verifyPaymentAction->execute($dto);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage(),
            ], 500);
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
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage(),
            ], 500);
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

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            \Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway,
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Handle payment success callback
     */
    public function successCallback(Request $request): JsonResponse
    {
        // This endpoint handles successful payment redirects
        $sessionId = $request->get('session_id');
        
        return response()->json([
            'success' => true,
            'message' => 'Payment successful',
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Handle payment cancellation
     */
    public function cancelCallback(Request $request): JsonResponse
    {
        // This endpoint handles cancelled payments
        return response()->json([
            'success' => false,
            'message' => 'Payment was cancelled',
        ]);
    }
}