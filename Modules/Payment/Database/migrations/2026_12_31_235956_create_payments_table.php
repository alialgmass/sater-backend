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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_order_id')->nullable();
            $table->unsignedBigInteger('vendor_order_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->enum('gateway', ['stripe', 'paymob', 'fawry', 'stc_pay', 'hyper_pay', 'local_bank'])->nullable();
            $table->enum('method', ['cod', 'credit_card', 'debit_card', 'wallet', 'bank_transfer', 'mobile_money']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded', 'expired']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('transaction_id')->nullable();
            $table->string('reference_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('metadata')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['transaction_id', 'reference_id']);
            $table->index(['customer_id', 'status']);
            $table->index(['vendor_order_id', 'status']);
            $table->foreign('vendor_order_id')->references('id')->on('vendor_orders')->onDelete('set null');
            $table->foreign('master_order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};