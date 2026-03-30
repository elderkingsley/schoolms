<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Admin's per-student comment shown on the report card
            $table->text('admin_comment')->nullable()->after('remark');

            // Teacher submission tracking
            $table->foreignId('submitted_by')
                ->nullable()
                ->after('admin_comment')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropColumn(['admin_comment', 'submitted_by', 'submitted_at']);
        });
    }
};
