<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->unsignedTinyInteger('ca_score')->default(0);    // Continuous Assessment
            $table->unsignedTinyInteger('exam_score')->default(0);
            $table->unsignedTinyInteger('total')->default(0);       // computed: ca + exam
            $table->string('grade')->nullable();                    // A, B, C...
            $table->string('remark')->nullable();                   // Excellent, Good...
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
