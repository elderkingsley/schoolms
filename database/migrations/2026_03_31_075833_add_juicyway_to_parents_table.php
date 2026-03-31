<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each parent gets a dedicated JuicyWay virtual bank account (NUBAN).
     * When a parent pays into their NUBAN, JuicyWay webhooks SchoolMS, which
     * matches the account_number to the parent, finds their unpaid invoice(s),
     * and records the payment automatically.
     *
     * The three-step provisioning flow (customer → wallet → NUBAN) mirrors
     * PayGrid's ProvisionJuicyWayWalletJob exactly.
     */
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->string('juicyway_customer_id')->nullable()->after('occupation');
            $table->string('juicyway_wallet_id')->nullable()->after('juicyway_customer_id');
            $table->string('juicyway_account_id')->nullable()->after('juicyway_wallet_id');
            $table->string('juicyway_account_number', 20)->nullable()->after('juicyway_account_id');
            $table->string('juicyway_bank_name')->nullable()->after('juicyway_account_number');
            $table->string('juicyway_bank_code', 10)->nullable()->after('juicyway_bank_name');
            $table->string('juicyway_wallet_status')->nullable()->after('juicyway_bank_code');
            // 'pending' | 'active' | 'failed'
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn([
                'juicyway_customer_id',
                'juicyway_wallet_id',
                'juicyway_account_id',
                'juicyway_account_number',
                'juicyway_bank_name',
                'juicyway_bank_code',
                'juicyway_wallet_status',
            ]);
        });
    }
};
