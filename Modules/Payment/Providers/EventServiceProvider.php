<?php

namespace Modules\Payment\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \Modules\Payment\Events\PaymentInitiated::class => [
            \Modules\Payment\Listeners\UpdateOrderPaymentStatusListener::class,
        ],
        \Modules\Payment\Events\PaymentSucceeded::class => [
            \Modules\Payment\Listeners\SendPaymentReceiptListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}