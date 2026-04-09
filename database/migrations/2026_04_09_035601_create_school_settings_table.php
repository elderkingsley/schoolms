<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Creates the school_settings table — a simple key/value store for
 * school-wide configuration that appears on invoices, reports and emails.
 *
 * Seeded with Nurtureville's known values so the invoice PDF works
 * immediately after migration without any manual setup.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed with Nurtureville's known values
        $now = now();
        DB::table('school_settings')->insert([
            ['key' => 'school_name',    'value' => 'Nurtureville School',         'created_at' => $now, 'updated_at' => $now],
            ['key' => 'school_tagline', 'value' => 'Nurturing Minds, Building Futures', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'school_address', 'value' => '',                             'created_at' => $now, 'updated_at' => $now],
            ['key' => 'school_email',   'value' => 'admin@nurturevilleschool.org', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'school_phone',   'value' => '',                             'created_at' => $now, 'updated_at' => $now],
            ['key' => 'school_website', 'value' => 'connect.nurturevilleschool.org', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'school_logo',    'value' => null,                           'created_at' => $now, 'updated_at' => $now],
            // Invoice-specific settings
            ['key' => 'invoice_bank_name',       'value' => '',  'created_at' => $now, 'updated_at' => $now],
            ['key' => 'invoice_account_name',    'value' => '',  'created_at' => $now, 'updated_at' => $now],
            ['key' => 'invoice_account_number',  'value' => '',  'created_at' => $now, 'updated_at' => $now],
            ['key' => 'invoice_payment_note',    'value' => "Please use your child's admission number as the payment reference for easy identification.", 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
