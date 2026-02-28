<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Super Admins table - completely isolated from regular users
        Schema::create('super_admins', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('full_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Global permissions table - feature toggles
        Schema::create('god_mode_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key', 100)->unique(); // e.g. 'transactions.access'
            $table->string('feature_name', 150); // Human readable name
            $table->string('feature_group', 50); // Group for UI organization
            $table->text('description')->nullable();
            $table->boolean('is_enabled_global')->default(true); // Global toggle
            $table->boolean('is_enabled_admin')->default(true); // Admin role
            $table->boolean('is_enabled_dealer')->default(true); // Dealer role
            $table->boolean('is_enabled_client')->default(false); // Client role
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Styles and branding table
        Schema::create('god_mode_styles', function (Blueprint $table) {
            $table->id();
            $table->string('style_key', 100)->unique(); // e.g. 'color_primary'
            $table->string('style_name', 150); // Human readable
            $table->string('style_group', 50); // 'colors', 'branding', etc.
            $table->string('style_type', 30); // 'color', 'image', 'text'
            $table->text('style_value')->nullable(); // The actual value
            $table->text('default_value')->nullable(); // Fallback
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Audit log for God Mode actions
        Schema::create('god_mode_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('super_admin_id')->constrained('super_admins')->onDelete('cascade');
            $table->string('action', 100); // e.g. 'permission.updated', 'style.changed'
            $table->string('target_type', 100)->nullable(); // Model class or table
            $table->unsignedBigInteger('target_id')->nullable(); // Target record ID
            $table->text('old_value')->nullable(); // JSON of old state
            $table->text('new_value')->nullable(); // JSON of new state
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['super_admin_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('god_mode_audit_logs');
        Schema::dropIfExists('god_mode_styles');
        Schema::dropIfExists('god_mode_permissions');
        Schema::dropIfExists('super_admins');
    }
};
