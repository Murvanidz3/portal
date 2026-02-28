<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            // Split make_model into make and model
            $table->string('make', 100)->nullable()->after('vin');
            $table->string('model', 100)->nullable()->after('make');
            
            // Document dates
            $table->date('document_received_at')->nullable()->after('arrival_date');
            $table->date('document_issued_at')->nullable()->after('document_received_at');
            
            // Shipping information
            $table->string('booking_number', 100)->nullable()->after('container_number');
            $table->string('shipping_line', 100)->nullable()->after('booking_number');
            $table->string('vessel', 100)->nullable()->after('shipping_line');
            $table->date('loading_date')->nullable()->after('vessel');
            $table->date('estimated_arrival_date')->nullable()->after('loading_date');
            
            // Dealer phone
            $table->string('dealer_phone', 50)->nullable()->after('client_phone');
            
            // Financial fields
            $table->decimal('dealer_profit', 10, 2)->default(0.00)->after('vehicle_cost');
            $table->decimal('discount', 10, 2)->default(0.00)->after('dealer_profit');
            $table->decimal('transfer_commission', 10, 2)->default(0.00)->after('discount');
        });
        
        // Migrate existing make_model data to make and model
        DB::statement("UPDATE cars SET make = SUBSTRING_INDEX(make_model, ' ', 1), model = SUBSTRING(make_model, LENGTH(SUBSTRING_INDEX(make_model, ' ', 1)) + 2) WHERE make_model IS NOT NULL AND make_model != ''");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn([
                'make',
                'model',
                'document_received_at',
                'document_issued_at',
                'booking_number',
                'shipping_line',
                'vessel',
                'loading_date',
                'estimated_arrival_date',
                'dealer_phone',
                'dealer_profit',
                'discount',
                'transfer_commission',
            ]);
        });
    }
};
