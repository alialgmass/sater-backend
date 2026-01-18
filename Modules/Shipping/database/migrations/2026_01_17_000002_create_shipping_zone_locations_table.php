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
        Schema::create('shipping_zone_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->string('country');
            $table->string('region')->nullable();
            $table->string('city')->nullable();
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
        Schema::dropIfExists('shipping_zone_locations');
    }
};