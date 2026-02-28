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
        // User shipping rate files - stores uploaded Excel files per user
        Schema::create('user_shipping_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('file_path'); // Path to uploaded Excel file
            $table->string('original_name'); // Original file name
            $table->timestamp('uploaded_at');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade'); // Admin who uploaded
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        // User shipping rates - parsed rates from Excel
        Schema::create('user_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_file_id')->constrained('user_shipping_files')->onDelete('cascade');
            $table->string('auction_type', 20); // 'copart' or 'iaai'
            $table->string('location_name', 200); // Location name from Excel
            $table->string('location_normalized', 200)->nullable(); // Normalized for searching
            $table->decimal('price', 10, 2); // Shipping price
            $table->timestamps();

            $table->index(['user_id', 'auction_type']);
            $table->index(['user_id', 'location_normalized']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_shipping_rates');
        Schema::dropIfExists('user_shipping_files');
    }
};
