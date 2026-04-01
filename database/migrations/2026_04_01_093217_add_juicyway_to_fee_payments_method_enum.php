<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add 'JuicyWay Transfer' to the fee_payments.method enum.
 *
 * The polling job records automated payments as 'JuicyWay Transfer'.
 * Without this migration, those inserts fail with an invalid enum value error.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE fee_payments
            MODIFY COLUMN method
            ENUM('Cash', 'Bank Transfer', 'POS', 'JuicyWay Transfer')
            NOT NULL DEFAULT 'Cash'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE fee_payments
            MODIFY COLUMN method
            ENUM('Cash', 'Bank Transfer', 'POS')
            NOT NULL DEFAULT 'Cash'
        ");
    }
};
