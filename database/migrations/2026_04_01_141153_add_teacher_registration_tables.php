<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two changes:
 *
 * 1. Add assistant_teacher_id to school_classes.
 *    Each class now supports two staff roles:
 *    - form_teacher_id   → the lead class teacher (already exists)
 *    - assistant_teacher_id → the teaching assistant (new)
 *
 * 2. Create teacher_registrations table.
 *    Stores self-registration requests from prospective staff.
 *    An admin reviews each request and approves or rejects it.
 *    On approval a User account is created and credentials emailed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Teaching assistant column on school_classes ────────────────────
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('assistant_teacher_id')
                ->nullable()
                ->after('form_teacher_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        // ── 2. Teacher self-registration queue ────────────────────────────────
        Schema::create('teacher_registrations', function (Blueprint $table) {
            $table->id();

            // Personal details submitted by the applicant
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            // Role they are applying for
            $table->enum('role', ['teacher', 'teaching_assistant'])
                ->default('teacher');

            // Optional: subject specialisation / notes from the applicant
            $table->text('notes')->nullable();

            // Admin workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            // Set when approved — links to the created User account
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Admin who reviewed the application
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['assistant_teacher_id']);
            $table->dropColumn('assistant_teacher_id');
        });

        Schema::dropIfExists('teacher_registrations');
    }
};
