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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('total_amount', 10, 2);
            $table->decimal('shipping_fees', 10, 2)->default(0);

            $table->enum('payment_method', ['cod', 'online']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index();

            $table->enum('status', [
                'pending', 'confirmed', 'shipped',
                'delivered', 'cancelled', 'returned'
            ])->default('pending')->index();

            $table->json('shipping_address');
            $table->string('shipping_provider')->nullable();
            $table->string('shipping_tracking')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
