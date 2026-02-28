<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN purpose ENUM(
                'balance_topup',
                'shipping',
                'vehicle_payment',
                'wallet_to_car',
                'car_to_car_out',
                'car_to_car_in',
                'other'
            ) DEFAULT 'other'");
        } else {
            // SQLite / other: purpose is likely string; no change needed
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN purpose ENUM(
                'balance_topup',
                'shipping',
                'vehicle_payment',
                'other'
            ) DEFAULT 'other'");
        }
    }
};
