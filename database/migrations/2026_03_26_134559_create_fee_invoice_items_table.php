<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_item_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');           // snapshot at time of invoicing
            $table->decimal('amount', 12, 2);      // snapshot at time of invoicing
            $table->enum('added_by', ['system', 'admin'])->default('system');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_invoice_items');
    }
};
