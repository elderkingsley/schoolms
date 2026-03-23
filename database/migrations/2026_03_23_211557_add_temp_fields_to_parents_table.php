<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->string('_temp_name')->nullable()->after('emergency_contact_relationship');
            $table->string('_temp_email')->nullable()->after('_temp_name');
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn(['_temp_name', '_temp_email']);
        });
    }
};
