<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend the messages table to support all requested recipient filter types.
     * MySQL ENUM doesn't support easy addition of values, so we change to string.
     * Also add term_id for "by term" filtering and recipient_count for stats.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Change enum to string to support all filter types without future migrations
            // Values: all, class, term, unpaid, individual
            $table->string('recipient_type')->default('all')->change();

            // Term filter — which term the message targets (optional)
            $table->foreignId('term_id')
                ->nullable()
                ->after('school_class_id')
                ->constrained()
                ->nullOnDelete();

            // Snapshot of how many parents were sent this message
            $table->unsignedInteger('recipient_count')->default(0)->after('term_id');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn(['term_id', 'recipient_count']);
            $table->enum('recipient_type', ['all', 'class', 'individual'])
                ->default('all')->change();
        });
    }
};
