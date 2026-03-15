<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sms_templates')
            ->where('status_key', 'green')
            ->update([
                'description' => 'გახსნილია',
            ]);
    }

    public function down(): void
    {
        DB::table('sms_templates')
            ->where('status_key', 'green')
            ->update([
                'description' => 'მწვანეშია',
            ]);
    }
};
