<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Add fee_item_id — links to the catalogue
            $table->foreignId('fee_item_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->nullOnDelete();

            // Make school_class_id required (every structure entry is class-specific)
            // item_name column can stay for legacy, fee_item_id is the new way
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['fee_item_id']);
            $table->dropColumn('fee_item_id');
        });
    }
};
