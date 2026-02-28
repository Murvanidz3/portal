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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('vin', 17);
            $table->string('auction_name', 50)->nullable();
            $table->string('auction_location', 100)->nullable();
            $table->string('make_model', 100);
            $table->integer('year')->nullable();
            $table->string('lot_number', 50)->nullable();
            $table->enum('status', ['purchased', 'warehouse', 'loaded', 'on_way', 'poti', 'green', 'delivered'])->default('purchased');
            $table->string('client_name', 100)->nullable();
            $table->string('client_id_number', 50)->nullable();
            $table->string('client_phone', 50)->nullable();
            $table->string('container_number', 50)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->decimal('vehicle_cost', 10, 2)->default(0.00);
            $table->decimal('auction_fee', 10, 2)->default(0.00);
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->decimal('additional_cost', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->string('main_photo', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('vin');
            $table->index('status');
            $table->index('lot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
