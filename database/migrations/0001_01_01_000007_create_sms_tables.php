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
        // SMS Templates
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('status_key', 50)->unique();
            $table->text('template_text');
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // SMS Logs
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id', 50)->nullable();
            $table->string('phone', 50);
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('sent');
            $table->timestamps();

            $table->index('phone');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_templates');
    }
};
