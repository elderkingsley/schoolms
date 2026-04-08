<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budpay_webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type');
            $table->string('reference')->nullable()->index();
            $table->longText('payload');
            $table->boolean('signature_valid')->default(false);
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamps();

            $table->index('event_type');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budpay_webhook_events');
    }
};
