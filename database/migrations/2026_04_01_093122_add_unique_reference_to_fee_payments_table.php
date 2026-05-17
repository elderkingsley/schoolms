<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        $duplicates = DB::table('fee_payments')
            ->select('reference', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('reference')
            ->where('reference', '!=', '')
            ->groupBy('reference')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('fee_payments')
                ->where('reference', $duplicate->reference)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('fee_payments', function (Blueprint $table) {
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
