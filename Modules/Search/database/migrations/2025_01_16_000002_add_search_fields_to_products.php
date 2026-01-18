<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add search-related indexes and columns to products table
     * This improves performance for search, filtering, and sorting operations
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add fields if they don't exist
            if (!Schema::hasColumn('products', 'keywords')) {
                $table->text('keywords')->nullable()->after('description');
            }

            if (!Schema::hasColumn('products', 'sales_count')) {
                $table->integer('sales_count')->default(0)->after('stock');
            }

            if (!Schema::hasColumn('products', 'avg_rating')) {
                $table->decimal('avg_rating', 3, 2)->default(0)->after('sales_count');
            }

            if (!Schema::hasColumn('products', 'rating_count')) {
                $table->integer('rating_count')->default(0)->after('avg_rating');
            }
        });

        // Create indexes for search performance
        Schema::table('products', function (Blueprint $table) {
            // Full-text search index
            $table->fullText(['name', 'description', 'keywords']);

            // Filter indexes
            $table->index(['category_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index(['price', 'status']);
            $table->index(['stock', 'status']);

            // Sorting indexes
            $table->index(['sales_count', 'status']);
            $table->index(['avg_rating', 'status']);
            $table->index(['created_at', 'status']);

            // Composite indexes
            $table->index(['vendor_id', 'category_id', 'status']);
            $table->index(['price', 'stock', 'status']);
            $table->index(['avg_rating', 'sales_count', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop indexes
            $table->dropFullText(['name', 'description', 'keywords']);
            $table->dropIndex(['category_id', 'status']);
            $table->dropIndex(['vendor_id', 'status']);
            $table->dropIndex(['price', 'status']);
            $table->dropIndex(['stock', 'status']);
            $table->dropIndex(['sales_count', 'status']);
            $table->dropIndex(['avg_rating', 'status']);
            $table->dropIndex(['created_at', 'status']);
            $table->dropIndex(['vendor_id', 'category_id', 'status']);
            $table->dropIndex(['price', 'stock', 'status']);
            $table->dropIndex(['avg_rating', 'sales_count', 'status']);

            // Drop columns
            $table->dropColumn(['keywords', 'sales_count', 'avg_rating', 'rating_count']);
        });
    }
};
