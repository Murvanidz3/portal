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
        // First, update existing 'port' values to 'warehouse' and 'terminal' to 'poti'
        DB::table('car_files')->where('category', 'port')->update(['category' => 'warehouse']);
        DB::table('car_files')->where('category', 'terminal')->update(['category' => 'poti']);
        
        // Change the column to string to support any category
        Schema::table('car_files', function (Blueprint $table) {
            $table->string('category', 50)->default('auction')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_files', function (Blueprint $table) {
            $table->enum('category', ['auction', 'port', 'terminal'])->default('auction')->change();
        });
    }
};
