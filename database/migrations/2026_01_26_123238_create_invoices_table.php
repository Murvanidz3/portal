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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->enum('type', ['vehicle', 'shipping']);
            $table->string('personal_id', 50)->nullable();
            $table->string('vin', 17)->nullable();
            $table->string('make_model', 100)->nullable();
            $table->integer('year')->nullable();
            $table->string('client_name', 100)->nullable();
            $table->decimal('vehicle_cost', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
