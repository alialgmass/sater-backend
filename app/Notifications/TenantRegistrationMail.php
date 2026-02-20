<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tenant Registration Mail Notification
 * 
 * Sends bilingual (Arabic/English) registration confirmation email
 * with verification link to newly registered tenants.
 */
class TenantRegistrationMail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     * 
     * @param Tenant $tenant
     * @param string $verificationUrl
     */
    public function __construct(
        protected Tenant $tenant,
        protected string $verificationUrl,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isArabic = $this->tenant->language === 'ar';
        
        return (new MailMessage())
            ->subject($this->getSubject($isArabic))
            ->greeting($this->getGreeting($isArabic))
            ->line($this->getIntroduction($isArabic))
            ->action($this->getActionText($isArabic), $this->verificationUrl)
            ->line($this->getExpiryInfo($isArabic))
            ->line($this->getClosing($isArabic));
    }

    /**
     * Get email subject based on language.
     */
    protected function getSubject(bool $isArabic): string
    {
        return $isArabic 
            ? 'مرحباً بك في منصتنا - تأكيد التسجيل' 
            : 'Welcome to Our Platform - Confirm Your Registration';
    }

    /**
     * Get email greeting based on language.
     */
    protected function getGreeting(bool $isArabic): string
    {
        return $isArabic 
            ? "مرحباً {$this->tenant->store_name}!" 
            : "Welcome {$this->tenant->store_name}!";
    }

    /**
     * Get email introduction based on language.
     */
    protected function getIntroduction(bool $isArabic): string
    {
        if ($isArabic) {
            return "شكراً لتسجيلك في منصتنا. لإكمال إعداد متجرك، يرجى التحقق من بريدك الإلكتروني من خلال النقر على الزر أدناه.";
        }
        
        return "Thank you for registering with our platform. To complete your store setup, please verify your email by clicking the button below.";
    }

    /**
     * Get action button text based on language.
     */
    protected function getActionText(bool $isArabic): string
    {
        return $isArabic 
            ? 'تحقق من بريدك الإلكتروني' 
            : 'Verify Your Email';
    }

    /**
     * Get expiry information based on language.
     */
    protected function getExpiryInfo(bool $isArabic): string
    {
        return $isArabic
            ? 'هذا الرابط صالح لمدة 24 ساعة.'
            : 'This link is valid for 24 hours.';
    }

    /**
     * Get closing text based on language.
     */
    protected function getClosing(bool $isArabic): string
    {
        return $isArabic
            ? 'إذا واجهت أي مشاكل، لا تتردد في التواصل معنا.'
            : 'If you encounter any issues, feel free to contact us.';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'store_name' => $this->tenant->store_name,
            'verification_url' => $this->verificationUrl,
        ];
    }
}
