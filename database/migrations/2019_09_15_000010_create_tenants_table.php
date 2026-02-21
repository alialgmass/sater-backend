<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // Custom fields for multi-tenancy feature
            $table->string('store_name');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('language')->default('en');
            $table->string('status')->default('pending_email_verification');
            $table->foreignId('current_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->text('suspension_reason')->nullable();
            $table->timestamp('deletion_scheduled_at')->nullable();

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
