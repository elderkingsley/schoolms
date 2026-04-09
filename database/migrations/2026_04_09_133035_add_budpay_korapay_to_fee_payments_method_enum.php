<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add 'BudPay Transfer' and 'Korapay Transfer' to the fee_payments.method enum.
 *
 * The column was originally:
 *   enum('Cash','Bank Transfer','POS','JuicyWay Transfer')
 *
 * We extend it to include the two new automated payment providers.
 * MySQL ALTER TABLE MODIFY is used directly — Laravel's enum() helper
 * rewrites the full enum definition so we must list all existing values too.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE fee_payments
            MODIFY COLUMN method
            ENUM(
                'Cash',
                'Bank Transfer',
                'POS',
                'JuicyWay Transfer',
                'BudPay Transfer',
                'Korapay Transfer'
            ) NOT NULL DEFAULT 'Cash'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE fee_payments
            MODIFY COLUMN method
            ENUM(
                'Cash',
                'Bank Transfer',
                'POS',
                'JuicyWay Transfer'
            ) NOT NULL DEFAULT 'Cash'
        ");
    }
};
