<?php
// Deploy to: database/migrations/2026_04_11_000001_add_result_type_to_school_classes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            // 'scored'      = CA (40%) + Exam (60%) format — all non-nursery classes
            // 'remark_only' = teacher types a free-text remark per subject — nursery only
            $table->enum('result_type', ['scored', 'remark_only'])
                  ->default('scored')
                  ->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn('result_type');
        });
    }
};
