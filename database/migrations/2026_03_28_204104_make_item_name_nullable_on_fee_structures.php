<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * item_name was part of the original fee_structures schema but is no longer
     * used — fee_item_id (FK to fee_items) is the current approach.
     * Making it nullable stops MySQL throwing "Field doesn't have a default value"
     * whenever FeeStructureManager saves a row without supplying item_name.
     */
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->string('item_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->string('item_name')->nullable(false)->change();
        });
    }
};
