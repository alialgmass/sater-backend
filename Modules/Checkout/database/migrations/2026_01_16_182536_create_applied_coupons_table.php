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
        Schema::create('applied_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkout_session_id')->nullable()->constrained('checkout_sessions')->cascadeOnDelete();
            $table->unsignedBigInteger('master_order_id')->nullable();
            $table->string('coupon_code');
            $table->decimal('discount_amount', 10, 2);
            $table->string('discount_type'); // percentage, fixed
            $table->timestamps();
            
            $table->index('master_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applied_coupons');
    }
};
