<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\SearchServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\VendorPanelProvider::class,
    Modules\Order\Providers\AuthServiceProvider::class,
];
