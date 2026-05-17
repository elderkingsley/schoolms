<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE fee_payments
            MODIFY COLUMN method
            ENUM(
                'Cash',
                'Bank Transfer',
                'POS',
                'JuicyWay Transfer',
                'BudPay Transfer',
                'Korapay Transfer',
                'Parent Credit'
            ) NOT NULL DEFAULT 'Cash'
        ");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

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
};
