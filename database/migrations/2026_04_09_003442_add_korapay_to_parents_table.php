<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Korapay virtual account columns to the parents table.
 *
 * Added alongside existing juicyway_* and budpay_* columns.
 * ParentGuardian::active_account_number accessor checks Korapay first.
 *
 * korapay_account_reference — our unique reference passed to Korapay
 *   on creation and returned in webhooks. Used to match payments.
 * korapay_account_number    — the permanent NUBAN
 * korapay_bank_name         — e.g. "Wema Bank"
 * korapay_bank_code         — e.g. "035"
 * korapay_wallet_status     — 'pending' | 'active' | 'failed'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->string('korapay_account_reference')->nullable()->after('budpay_wallet_status');
            $table->string('korapay_account_number', 20)->nullable()->after('korapay_account_reference');
            $table->string('korapay_bank_name')->nullable()->after('korapay_account_number');
            $table->string('korapay_bank_code', 10)->nullable()->after('korapay_bank_name');
            $table->string('korapay_wallet_status')->nullable()->after('korapay_bank_code');

            $table->index('korapay_account_reference', 'parents_korapay_ref_index');
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropIndex('parents_korapay_ref_index');
            $table->dropColumn([
                'korapay_account_reference',
                'korapay_account_number',
                'korapay_bank_name',
                'korapay_bank_code',
                'korapay_wallet_status',
            ]);
        });
    }
};
