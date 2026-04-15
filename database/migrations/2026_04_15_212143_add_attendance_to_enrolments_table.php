<?php
// Deploy to: database/migrations/2026_04_15_100002_add_attendance_to_enrolments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds per-student per-term attendance to enrolments.
 *
 * WHY enrolments and not a separate table?
 * An enrolment is already the one record that ties a student to a class
 * for an academic session — it is the correct anchor for term-level data
 * like attendance. No new join is needed in the controller or PDF.
 *
 * times_present and times_absent are stored independently.
 * The teacher enters both values. They are NOT derived from each other.
 * This matches Nigerian school register practice where absence reasons
 * may not perfectly reconcile with the school calendar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrolments', function (Blueprint $table) {
            $table->unsignedSmallInteger('times_present')->nullable()->after('status');
            $table->unsignedSmallInteger('times_absent')->nullable()->after('times_present');
        });
    }

    public function down(): void
    {
        Schema::table('enrolments', function (Blueprint $table) {
            $table->dropColumn(['times_present', 'times_absent']);
        });
    }
};
