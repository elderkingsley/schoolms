<?php
// Deploy to: database/migrations/2026_04_25_000001_add_misc_invoice_support.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            // Make term_id nullable — miscellaneous invoices have no term
            $table->foreignId('term_id')->nullable()->change();

            // Invoice type — 'school_fee' (default) or 'miscellaneous'
            $table->enum('invoice_type', ['school_fee', 'miscellaneous'])
                ->default('school_fee')
                ->after('term_id');

            // Description for miscellaneous invoices (e.g. "School Uniform 2026")
            $table->string('description')->nullable()->after('invoice_type');

            // Drop the unique(student_id, term_id) constraint — a student can
            // have multiple miscellaneous invoices, so we can no longer enforce
            // uniqueness this way. School fee invoices are still protected by
            // application-level checks in FeeService::generateInvoiceForStudent().
            $table->dropUnique(['student_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_type', 'description']);
            $table->foreignId('term_id')->nullable(false)->change();
            $table->unique(['student_id', 'term_id']);
        });
    }
};
