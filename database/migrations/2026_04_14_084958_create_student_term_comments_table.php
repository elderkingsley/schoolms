<?php
// Deploy to: database/migrations/2026_04_14_000001_create_student_term_comments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores general per-student per-term comments from the class teacher
     * and the head teacher. These are separate from the per-subject results
     * rows because a general comment is about the whole child, not one subject.
     *
     * One row per student per term — enforced by the unique constraint.
     *
     * teacher_comment      — written by the form teacher in the teacher portal
     * head_teacher_comment — written by admin before publishing
     */
    public function up(): void
    {
        Schema::create('student_term_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();

            $table->text('teacher_comment')->nullable();
            $table->text('head_teacher_comment')->nullable();

            // Who wrote each comment
            $table->foreignId('written_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('submitted_at')->nullable(); // when teacher submitted

            $table->timestamps();

            $table->unique(['student_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_term_comments');
    }
};
