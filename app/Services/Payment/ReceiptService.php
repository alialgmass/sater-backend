<?php

namespace App\Services\Payment;

use App\Models\Payment\Payment;
use App\Models\Payment\PaymentReceipt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;

class ReceiptService
{
    public function generateAndSend(Payment $payment): void
    {
        // Generate the receipt
        $receipt = $this->generateReceipt($payment);

        if ($receipt) {
            // Send the receipt to the customer
            $this->sendReceipt($payment, $receipt);
        }
    }

    /**
     * Generate a payment receipt
     */
    public function generateReceipt(Payment $payment): ?PaymentReceipt
    {
        try {
            // Create PDF receipt
            $pdf = PdfFacade::loadView('payments.receipt', [
                'payment' => $payment,
                'order' => $payment->vendorOrder,
                'customer' => $payment->customer,
            ]);

            // Define file path
            $fileName = "receipt_{$payment->id}_" . now()->format('Y-m-d_H-i-s') . '.pdf';
            $filePath = "receipts/{$fileName}";

            // Store the PDF
            Storage::disk('public')->put($filePath, $pdf->output());

            // Create receipt record
            $receipt = PaymentReceipt::create([
                'payment_id' => $payment->id,
                'receipt_number' => 'RCPT-' . strtoupper(Str::random(8)),
                'receipt_type' => 'pdf',
                'file_path' => $filePath,
                'file_url' => Storage::url($filePath),
                'email_address' => $payment->customer->email ?? null,
            ]);

            return $receipt;
        } catch (\Exception $e) {
            \Log::error('Failed to generate payment receipt', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            return null;
        }
    }

    /**
     * Send receipt to customer
     */
    public function sendReceipt(Payment $payment, PaymentReceipt $receipt): void
    {
        try {
            // Send email with receipt
            Mail::to($payment->customer->email)
                ->send(new \App\Mail\PaymentReceiptMail($payment, $receipt));

            // Update receipt sent status
            $receipt->update([
                'sent_to_customer' => true,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send payment receipt', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'receipt_id' => $receipt->id,
            ]);
        }
    }

    /**
     * Generate HTML receipt
     */
    public function generateHtmlReceipt(Payment $payment): string
    {
        return view('payments.html-receipt', [
            'payment' => $payment,
            'order' => $payment->vendorOrder,
            'customer' => $payment->customer,
        ])->render();
    }
}