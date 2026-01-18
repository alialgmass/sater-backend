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
        Schema::create('vendor_free_shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->decimal('min_order_amount', 10, 2);
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
        Schema::dropIfExists('vendor_free_shipping_rules');
    }
};