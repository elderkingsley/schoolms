<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_pending_status_to_students_table.php

    public function up(): void
    {
        // MySQL doesn't allow easy enum modification — change to string instead
        Schema::table('students', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            // Add fields needed for enrolment form review
            $table->string('class_applied_for')->nullable()->after('status');
            $table->text('medical_notes')->nullable()->after('class_applied_for');
            $table->timestamp('approved_at')->nullable()->after('medical_notes');
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['class_applied_for', 'medical_notes', 'approved_at', 'approved_by']);
        });
    }
};
