<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Checkout\Models\CheckoutSession;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Cart\Services\StockValidationService;
use Illuminate\Validation\ValidationException;

class OrderCreationService
{
    public function __construct(
        protected StockValidationService $stockValidation,
        protected TaxCalculationService $taxService,
        protected ShippingCalculationService $shippingService
    ) {}

    public function createOrder(CheckoutSession $session): Order
    {
        return DB::transaction(function () use ($session) {
            // Get cart items
            $items = $this->getCartItems($session);

            // Revalidate stock
            $this->validateStock($items);

            // Create master order
            $masterOrder = $this->createMasterOrder($session);

            // Split by vendor and create vendor orders
            $vendorOrders = $this->splitOrdersByVendor($masterOrder, $items, $session);

            // Lock stock
            $this->lockStock($items);

            // Mark session as completed
            $session->update(['status' => 'completed']);

            // Transfer coupons to master order
            $session->appliedCoupons()->update(['master_order_id' => $masterOrder->id]);

            return $masterOrder->load('vendorOrders.items');
        });
    }

    protected function getCartItems(CheckoutSession $session)
    {
        if ($session->customer_id) {
            $cart = \Modules\Cart\Models\Cart::where('customer_id', $session->customer_id)->first();
            return $cart ? $cart->items()->with(['product', 'vendor'])->get() : collect();
        } else {
            return \Modules\Cart\Models\GuestCart::byCartKey($session->cart_key)
                ->with(['product', 'vendor'])
                ->get();
        }
    }

    protected function validateStock($items): void
    {
        foreach ($items as $item) {
            if (!$this->stockValidation->validateStock($item->product, $item->quantity)) {
                throw ValidationException::withMessages([
                    'stock' => "Insufficient stock for product: {$item->product->name}"
                ]);
            }
        }
    }

    protected function createMasterOrder(CheckoutSession $session): Order
    {
        return Order::create([
            'order_number' => $this->generateOrderNumber(),
            'customer_id' => $session->customer_id,
            'email' => $session->email,
            'phone' => $session->phone,
            'shipping_address' => $session->shipping_address,
            'total_amount' => $session->total,
            'shipping_fees' => $session->shipping,
            'tax' => $session->tax,
            'discount' => $session->discount,
            'payment_method' => $session->payment_method,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    protected function splitOrdersByVendor(Order $masterOrder, $items, CheckoutSession $session): array
    {
        $vendorGroups = $items->groupBy('vendor_id');
        $vendorOrders = [];

        foreach ($vendorGroups as $vendorId => $vendorItems) {
            $subtotal = $vendorItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            $tax = $this->taxService->calculateTax($subtotal, $session->shipping_address, $vendorId);
            $shipping = $this->shippingService->calculateShipping(
                $vendorItems->toArray(),
                $session->shipping_method,
                $session->shipping_address
            );

            $vendorOrder = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'parent_order_id' => $masterOrder->id,
                'vendor_id' => $vendorId,
                'customer_id' => $session->customer_id,
                'email' => $session->email,
                'phone' => $session->phone,
                'shipping_address' => $session->shipping_address,
                'total_amount' => $subtotal + $tax + $shipping,
                'shipping_fees' => $shipping,
                'tax' => $tax,
                'discount' => 0,
                'payment_method' => $session->payment_method,
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($vendorItems as $item) {
                OrderItem::create([
                    'order_id' => $vendorOrder->id,
                    'product_id' => $item->product_id,
                    'vendor_id' => $vendorId,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'subtotal' => $item->product->price * $item->quantity,
                ]);
            }

            $vendorOrders[] = $vendorOrder;
        }

        return $vendorOrders;
    }

    protected function lockStock($items): void
    {
        foreach ($items as $item) {
            // Stub: Lock stock in database
            // In production: decrement product stock with row-level locking
            DB::table('products')
                ->where('id', $item->product_id)
                ->lockForUpdate()
                ->decrement('stock', $item->quantity);
        }
    }

    public function releaseStock(array $items): void
    {
        foreach ($items as $item) {
            DB::table('products')
                ->where('id', $item['product_id'])
                ->increment('stock', $item['quantity']);
        }
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(Str::random(10));
    }
}
