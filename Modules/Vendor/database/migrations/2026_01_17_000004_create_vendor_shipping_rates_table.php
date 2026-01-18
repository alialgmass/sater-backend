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
        Schema::create('vendor_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_shipping_method_id')->constrained('vendor_shipping_methods')->onDelete('cascade');
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->decimal('min_weight', 8, 2)->default(0);
            $table->decimal('max_weight', 8, 2)->nullable(); // nullable means unlimited
            $table->decimal('price', 10, 2);
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
        Schema::dropIfExists('vendor_shipping_rates');
    }
};