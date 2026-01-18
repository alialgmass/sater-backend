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
        Schema::create('guest_carts', function (Blueprint $table) {
            $table->id();
            $table->uuid('cart_key');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_add_time', 10, 2);
            $table->string('status')->default('available');
            $table->timestamps();
            
            $table->index('cart_key');
            $table->index(['cart_key', 'vendor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_carts');
    }
};
