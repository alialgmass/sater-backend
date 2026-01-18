<?php

namespace Modules\Order\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Order\Enums\VendorOrderStatusEnum;
use Modules\Order\Models\VendorOrder;

class OrderStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected VendorOrder $vendorOrder,
        protected VendorOrderStatusEnum $newStatus
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order #{$this->vendorOrder->vendor_order_number} Status Updated")
            ->line("The status of your order #{$this->vendorOrder->vendor_order_number} has been updated.")
            ->line("New Status: " . ucfirst(str_replace('_', ' ', $this->newStatus->value)))
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
            'old_status' => $this->vendorOrder->getOriginal('status'),
            'new_status' => $this->newStatus->value,
            'message' => "Order #{$this->vendorOrder->vendor_order_number} status updated to {$this->newStatus->value}",
        ];
    }
}