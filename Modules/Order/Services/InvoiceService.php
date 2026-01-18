<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Order\Models\Order;
use Spatie\LaravelPdf\Facades\Pdf;

class InvoiceService
{
    public function generate(Order $order)
    {
        $cacheKey = 'invoice_' . $order->order_number;
        $pdf = Cache::get($cacheKey);

        if (!$pdf) {
            $pdf = Pdf::view('order::invoice', ['order' => $order])
                ->format('a4')
                ->name('invoice-' . $order->order_number . '.pdf');

            // Cache for 24 hours
            Cache::put($cacheKey, $pdf, 60 * 24);
        }

        return $pdf;
    }
}
