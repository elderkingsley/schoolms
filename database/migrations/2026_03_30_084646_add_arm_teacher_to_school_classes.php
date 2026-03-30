<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            // Free-text arm name e.g. "Gold", "Silver", "Red", "Blue", "Emerald"
            // Null means no arm — the class has no subdivision
            $table->string('arm')->nullable()->after('level');

            // Form teacher — one teacher assigned to the whole class
            $table->foreignId('form_teacher_id')
                ->nullable()
                ->after('arm')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['form_teacher_id']);
            $table->dropColumn(['arm', 'form_teacher_id']);
        });
    }
};
