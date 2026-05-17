<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_credit_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_credit_id')->constrained('parent_credits')->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained('fee_invoices')->cascadeOnDelete();
            $table->foreignId('fee_payment_id')->nullable()->constrained('fee_payments')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('reference', 120)->unique();
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['parent_credit_id', 'fee_invoice_id'], 'parent_credit_invoice_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_credit_applications');
    }
};
