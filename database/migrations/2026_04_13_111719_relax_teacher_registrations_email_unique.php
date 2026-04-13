<?php
// Deploy to: database/migrations/2026_04_11_000003_relax_teacher_registrations_email_unique.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original migration added a hard UNIQUE constraint on
     * teacher_registrations.email. This blocks two legitimate scenarios:
     *
     * 1. A parent registering as a teacher — their email already exists
     *    in the users table, and if they previously submitted (even a
     *    rejected) registration, the DB constraint fires before our
     *    PHP validation can give a friendly message.
     *
     * 2. A rejected applicant resubmitting — a rejection should not
     *    permanently bar someone from applying again.
     *
     * The uniqueness logic is now enforced entirely in PHP
     * (StaffRegistrationForm) — only pending/approved registrations
     * block a new submission. The DB constraint is dropped here.
     */
    public function up(): void
    {
        Schema::table('teacher_registrations', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
    }

    public function down(): void
    {
        Schema::table('teacher_registrations', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
