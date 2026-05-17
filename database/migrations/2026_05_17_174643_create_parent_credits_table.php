<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('origin_fee_invoice_id')->nullable()->constrained('fee_invoices')->nullOnDelete();
            $table->string('source_reference', 120)->unique();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('balance_amount', 12, 2);
            $table->string('status', 20)->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['parent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_credits');
    }
};
