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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->unique()->after('id');
            $table->foreignId('parent_order_id')->nullable()->after('id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->after('customer_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('email')->after('customer_id');
            $table->string('phone')->after('email');
            $table->decimal('tax', 10, 2)->default(0)->after('shipping_fees');
            $table->decimal('discount', 10, 2)->default(0)->after('tax');
            
            $table->index('order_number');
            $table->index(['parent_order_id', 'vendor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'parent_order_id', 'vendor_id', 'email', 'phone', 'tax', 'discount']);
        });
    }
};
