<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            // The reference we send to JuicyWay — used to match the webhook
            // back to this specific invoice. Format: INV-{id}-T{term_id}
            $table->string('payment_link_reference', 50)
                ->nullable()->unique()->after('sent_at');

            // JuicyWay's internal payment link ID (for PATCH deactivation later)
            $table->string('payment_link_id')->nullable()
                ->after('payment_link_reference');

            // The clickable URL embedded in parent emails
            $table->string('payment_link_url')->nullable()
                ->after('payment_link_id');

            // When the payment link was successfully created
            $table->timestamp('payment_link_generated_at')->nullable()
                ->after('payment_link_url');

            // Idempotency flag — prevents double-processing same webhook event
            $table->boolean('juicyway_payment_processed')->default(false)
                ->after('payment_link_generated_at');

            // Track payment link creation failures for retry/alerting
            $table->text('payment_link_error')->nullable()
                ->after('juicyway_payment_processed');
        });

        // fee_payments.method is an enum — JuicyWay payments need a 'JuicyWay' value.
        // Change to string so we can add any method without future migrations.
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->string('method')->default('Cash')->change();
        });
    }

    public function down(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'payment_link_reference',
                'payment_link_id',
                'payment_link_url',
                'payment_link_generated_at',
                'juicyway_payment_processed',
                'payment_link_error',
            ]);
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->enum('method', ['Cash', 'Bank Transfer', 'POS'])
                ->default('Cash')->change();
        });
    }
};
