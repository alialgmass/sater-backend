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
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->string('receipt_number');
            $table->enum('receipt_type', ['pdf', 'html']);
            $table->string('file_path')->nullable();
            $table->string('file_url')->nullable();
            $table->boolean('sent_to_customer')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->string('email_address')->nullable();
            $table->timestamps();
            
            $table->unique('receipt_number');
            $table->index('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};