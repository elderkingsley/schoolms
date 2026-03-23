<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('provider');           // 'paystack' or 'monnify'
            $table->string('reference')->unique(); // provider transaction ref
            $table->string('virtual_account_number')->nullable(); // Monnify
            $table->string('virtual_account_bank')->nullable();   // Monnify
            $table->string('virtual_account_name')->nullable();   // Monnify
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending|success|failed
            $table->json('provider_response')->nullable(); // raw webhook payload
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_references');
    }
};
