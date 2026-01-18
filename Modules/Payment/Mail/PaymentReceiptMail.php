<?php

namespace Modules\Payment\Mail;

use Modules\Payment\Models\Payment;
use Modules\Payment\Models\PaymentReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public PaymentReceipt $receipt
    ) {}

    public function build()
    {
        return $this->subject("Payment Receipt - Order #{$this->payment->vendorOrder->order_number}")
            ->view('emails.payment-receipt')
            ->attach(storage_path('app/public/' . $this->receipt->file_path), [
                'as' => 'payment-receipt.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}