<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add Islamic clothing-specific attributes columns to products
     * These are stored as JSON for flexibility and to support future extensibility
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'clothing_attributes')) {
                $table->json('clothing_attributes')->nullable()->after('attributes')->comment('Islamic clothing specific: fabric_type, sleeve_length, opacity_level, hijab_style');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('clothing_attributes');
        });
    }
};
