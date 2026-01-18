<?php

namespace Modules\Order\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Order\Models\VendorOrder;

class NewVendorOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected VendorOrder $vendorOrder
    ) {
        // Force queue only as required by specification
        $this->afterCommit = true;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail', 'database']; // Adding 'sms' would require a package like 'laravel-notification-channels/twilio'
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Order #{$this->vendorOrder->vendor_order_number}")
            ->line("You have received a new order.")
            ->line("Order Number: {$this->vendorOrder->vendor_order_number}")
            ->line("Item Count: {$this->vendorOrder->items->count()}")
            ->line("COD: " . ($this->vendorOrder->is_cod ? 'Yes' : 'No'))
            ->action('View Order', url("/vendor/orders/{$this->vendorOrder->vendor_order_number}"))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'vendor_order_id' => $this->vendorOrder->id,
            'vendor_order_number' => $this->vendorOrder->vendor_order_number,
            'item_count' => $this->vendorOrder->items->count(),
            'is_cod' => $this->vendorOrder->is_cod,
            'total_amount' => $this->vendorOrder->total_amount,
            'message' => "New order #{$this->vendorOrder->vendor_order_number} received",
        ];
    }
}