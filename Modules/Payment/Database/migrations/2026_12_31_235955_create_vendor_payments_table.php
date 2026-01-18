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
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_order_id');
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded', 'expired']);
            $table->unsignedBigInteger('last_payment_attempt_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->unique('vendor_order_id');
            $table->foreign('vendor_order_id')->references('id')->on('vendor_orders')->onDelete('cascade');
            $table->foreign('last_payment_attempt_id')->references('id')->on('payment_attempts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
    }
};