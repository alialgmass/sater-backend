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
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->integer('attempt_number');
            $table->enum('gateway', ['stripe', 'paymob', 'fawry', 'stc_pay', 'hyper_pay', 'local_bank'])->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded', 'expired']);
            $table->json('request_data');
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['payment_id', 'attempt_number']);
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};