<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('code');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
        });

        // Seed initial sort_order for existing rows alphabetically
        $i = 1;
        DB::table('subjects')->orderBy('name')->get(['id'])
            ->each(function ($row) use (&$i) {
                DB::table('subjects')->where('id', $row->id)->update(['sort_order' => $i++]);
            });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'sort_order']);
        });
    }
};
