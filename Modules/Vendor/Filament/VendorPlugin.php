<?php

namespace Modules\Vendor\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class VendorPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Vendor';
    }

    public function getId(): string
    {
        return 'vendor';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
