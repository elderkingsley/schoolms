<?php
// Deploy to: database/migrations/2026_04_15_100003_create_student_trait_scores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores Psychomotor Skills and Affective Area ratings per student per term.
 *
 * KEY-VALUE DESIGN: each trait is a separate row (trait_key + score).
 * This avoids 17 nullable columns and makes adding future traits a data
 * change, not a schema change. The PHP model's constants drive everything.
 *
 * NORMALISED TO 5 POINTS for both Primary and Preschool:
 * - Primary report card shows a 6-label key (1=Not Applicable...5=Very Good)
 *   but teachers only enter 1–5.
 * - Preschool uses 1=Poor...5=Excellent.
 * The PDF templates display the correct label key per class type.
 *
 * score NULL means the teacher left the trait blank (not rated this term).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_trait_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->string('trait_key', 60);               // e.g. 'handwriting', 'punctuality'
            $table->unsignedTinyInteger('score')->nullable(); // 1–5, null = not rated
            $table->timestamps();

            $table->unique(['student_id', 'term_id', 'trait_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_trait_scores');
    }
};
