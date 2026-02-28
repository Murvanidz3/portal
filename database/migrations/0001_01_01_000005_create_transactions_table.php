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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->nullable()->constrained('cars')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->date('payment_date');
            $table->enum('purpose', ['balance_topup', 'shipping', 'vehicle_payment', 'other'])->default('other');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('car_id');
            $table->index('user_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
