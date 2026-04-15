<?php
// Deploy to: database/migrations/2026_04_15_100001_add_term_metadata_to_terms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds two columns to the terms table:
 *
 * school_days_count — how many days the school was open this term.
 *                     Set by admin in the Sessions & Terms manager.
 *                     Printed on the report card as "No. of Times School Opened".
 *
 * next_term_begins  — the date that appears on the report card under
 *                     "Next Term Begins". Set by admin before generating cards.
 *
 * Both nullable so existing term rows stay valid with no data entry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            $table->unsignedSmallInteger('school_days_count')->nullable()->after('end_date');
            $table->date('next_term_begins')->nullable()->after('school_days_count');
        });
    }

    public function down(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            $table->dropColumn(['school_days_count', 'next_term_begins']);
        });
    }
};
