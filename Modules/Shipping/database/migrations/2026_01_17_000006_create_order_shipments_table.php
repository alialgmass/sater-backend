<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shipping_method_id')->constrained('vendor_shipping_methods')->onDelete('cascade');
            $table->string('courier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->enum('status', ['pending', 'shipped', 'delivered', 'failed'])->default('pending');
            $table->timestamp('estimated_delivery_from')->nullable();
            $table->timestamp('estimated_delivery_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_shipments');
    }
};