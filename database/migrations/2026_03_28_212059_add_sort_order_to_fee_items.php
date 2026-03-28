<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
        });

        // Seed initial sort_order values for existing rows so nothing
        // jumps around after the migration runs on production.
        // Compulsory items first (in current name order), then optional.
        $i = 1;
        DB::table('fee_items')
            ->orderBy('type')       // compulsory before optional (c < o alphabetically)
            ->orderBy('name')
            ->get(['id'])
            ->each(function ($row) use (&$i) {
                DB::table('fee_items')->where('id', $row->id)->update(['sort_order' => $i++]);
            });
    }

    public function down(): void
    {
        Schema::table('fee_items', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
