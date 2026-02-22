<?php

namespace Modules\Auth\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Auth\Services\OtpService;
use Modules\Customer\Models\Customer;

class SendOtpForRegistration
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private OtpService $otpService,
    )
    {
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        /**
         * @var Customer $customer
         */
        $customer = $event->user;
        $otp = $this->otpService->generate($customer);

    }
}
