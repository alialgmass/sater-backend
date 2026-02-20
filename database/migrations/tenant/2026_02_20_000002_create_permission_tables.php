<?php

declare(strict_types=1);

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
        // Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            
            $table->unique(['name', 'guard_name']);
        });

        // Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            
            $table->unique(['name', 'guard_name']);
        });

        // Model has permissions
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->string('model_type');
            $table->uuid('model_id');
            
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_primary');
            
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });

        // Model has roles
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->string('model_type');
            $table->uuid('model_id');
            
            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_primary');
            
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

        // Role has permissions
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');
            
            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_primary');
            
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
            
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

        // Cache for permissions
        Schema::create('permission_cache', function (Blueprint $table) {
            $table->string('model_type');
            $table->uuid('model_id');
            $table->string('permission_name');
            $table->string('guard_name');
            $table->timestamps();
            
            $table->unique(['model_id', 'model_type', 'permission_name', 'guard_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_cache');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
