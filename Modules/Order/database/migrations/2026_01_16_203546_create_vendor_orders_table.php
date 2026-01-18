<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default(\Modules\Order\Enums\VendorOrderStatusEnum::CONFIRMED->value);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3);
            $table->json('shipping_address');
            $table->string('shipping_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_orders');
    }
};
