<?php
// Deploy to: database/migrations/2026_04_11_000002_make_result_scores_nullable.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nursery classes use remark-only results — no CA or exam scores.
     * We make ca_score, exam_score, total, and grade nullable so a result
     * row can exist with only a remark filled in.
     *
     * Existing scored results are unaffected — their values stay as-is.
     */
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->unsignedTinyInteger('ca_score')->nullable()->default(null)->change();
            $table->unsignedTinyInteger('exam_score')->nullable()->default(null)->change();
            $table->unsignedTinyInteger('total')->nullable()->default(null)->change();
            $table->string('grade')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->unsignedTinyInteger('ca_score')->nullable(false)->default(0)->change();
            $table->unsignedTinyInteger('exam_score')->nullable(false)->default(0)->change();
            $table->unsignedTinyInteger('total')->nullable(false)->default(0)->change();
            $table->string('grade')->nullable()->change(); // grade was already nullable
        });
    }
};
