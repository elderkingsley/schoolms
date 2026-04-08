<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add BudPay virtual account columns to the parents table.
 *
 * These are added alongside the existing juicyway_* columns — we do NOT
 * remove JuicyWay columns yet. The pilot keeps both sets of columns so
 * we can compare and roll back if needed. Full JuicyWay column removal
 * comes in a separate migration after the pilot is confirmed successful.
 *
 * BudPay provisioning is a 2-step flow (vs JuicyWay's 3-step):
 *   budpay_customer_code  — returned from POST /customer
 *   budpay_account_number — returned from POST /dedicated_virtual_account
 *   budpay_bank_name      — returned alongside account_number
 *   budpay_bank_code      — returned alongside account_number
 *   budpay_wallet_status  — 'pending' | 'active' | 'failed'
 *
 * Note: BudPay has no separate wallet_id — the customer_code is the
 * only ID needed for the dedicated account assignment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->string('budpay_customer_code')->nullable()->after('juicyway_wallet_status');
            $table->string('budpay_account_number', 20)->nullable()->after('budpay_customer_code');
            $table->string('budpay_bank_name')->nullable()->after('budpay_account_number');
            $table->string('budpay_bank_code', 10)->nullable()->after('budpay_bank_name');
            $table->string('budpay_wallet_status')->nullable()->after('budpay_bank_code');
            // 'pending' | 'active' | 'failed'
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn([
                'budpay_customer_code',
                'budpay_account_number',
                'budpay_bank_name',
                'budpay_bank_code',
                'budpay_wallet_status',
            ]);
        });
    }
};
