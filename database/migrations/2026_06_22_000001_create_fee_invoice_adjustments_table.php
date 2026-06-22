<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_invoice_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_invoice_id')->constrained('fee_invoices')->cascadeOnDelete();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->decimal('old_total_amount', 12, 2);
            $table->decimal('new_total_amount', 12, 2);
            $table->decimal('old_amount_paid', 12, 2);
            $table->decimal('new_amount_paid', 12, 2);
            $table->decimal('old_balance', 12, 2);
            $table->decimal('new_balance', 12, 2);
            $table->decimal('credit_adjustment_amount', 12, 2)->default(0);
            $table->string('paygrid_sync_status', 20)->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->json('before_snapshot');
            $table->json('after_snapshot');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['fee_invoice_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_invoice_adjustments');
    }
};
