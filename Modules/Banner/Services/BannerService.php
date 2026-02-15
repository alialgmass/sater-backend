<?php

namespace Modules\Banner\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Banner\Models\Banner;

class BannerService
{
    /**
     * Get all active and currently running banners with caching
     */
    public function getActive(): Collection
    {
        return Cache::remember('banners_active_' . app()->getLocale(), 300, function () {
            return Banner::active()
                ->currentlyRunning()
                ->orderBy('sort_order')
                ->with('products')
                ->get();
        });
    }

    /**
     * Get all banners for admin/internal use
     */
    public function getAll(): Collection
    {
        return Banner::orderBy('sort_order')->get();
    }
}
