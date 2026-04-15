<?php
// Deploy to: database/migrations/2026_04_15_100004_update_results_for_report_card.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Three changes to the results table:
 *
 * 1. remark → TEXT
 *    Preschool evaluations are multi-sentence paragraphs. VARCHAR(255) is too short.
 *
 * 2. class_average / class_lowest / class_highest
 *    Pre-computed per-subject class statistics, stored at admin publish time.
 *    Nullable — preschool (remark-only) classes never populate these.
 *    class_average is DECIMAL(5,2) to support values like 87.33.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->text('remark')->nullable()->change();
            $table->decimal('class_average', 5, 2)->nullable()->after('remark');
            $table->unsignedTinyInteger('class_lowest')->nullable()->after('class_average');
            $table->unsignedTinyInteger('class_highest')->nullable()->after('class_lowest');
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->string('remark', 255)->nullable()->change();
            $table->dropColumn(['class_average', 'class_lowest', 'class_highest']);
        });
    }
};
