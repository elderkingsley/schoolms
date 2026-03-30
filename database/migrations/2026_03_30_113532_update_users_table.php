<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // MySQL doesn't allow easy enum modification so we change to string
            // (already done for students.status in a previous migration — same pattern)
            $table->string('user_type')->default('parent')->change();

            // Force new users to change their temp password on first login
            $table->boolean('force_password_change')->default(false)->after('is_active');

            // Optional phone number — useful for teacher and admin accounts
            $table->string('phone')->nullable()->after('force_password_change');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['super_admin', 'admin', 'teacher', 'parent'])
                ->default('parent')->change();
            $table->dropColumn(['force_password_change', 'phone']);
        });
    }
};
