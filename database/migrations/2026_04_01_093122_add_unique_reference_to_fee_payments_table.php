<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add a unique constraint on fee_payments.reference for non-null values.
 *
 * This is the database-level idempotency guard. Even if two processes
 * pass the application-level exists() check simultaneously, the database
 * will reject the second INSERT with a UniqueConstraintViolationException,
 * which FeeService::recordPayment() catches and handles gracefully.
 *
 * NULL references (cash payments with no bank reference) are excluded
 * from the unique constraint — multiple cash payments can have null refs.
 * MySQL/MariaDB treats each NULL as distinct in unique indexes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // First clean up any existing duplicate references (from testing)
        // before adding the constraint — otherwise the migration will fail.
        DB::statement("
            DELETE fp1 FROM fee_payments fp1
            INNER JOIN fee_payments fp2
                ON fp1.reference = fp2.reference
                AND fp1.id > fp2.id
            WHERE fp1.reference IS NOT NULL
              AND fp1.reference != ''
        ");

        Schema::table('fee_payments', function (Blueprint $table) {
            // Partial unique index — only applies to non-null, non-empty references
            $table->unique('reference', 'fee_payments_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropUnique('fee_payments_reference_unique');
        });
    }
};
