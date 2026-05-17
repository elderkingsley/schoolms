<?php
// Deploy to: database/migrations/2026_04_25_000001_add_misc_invoice_support.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->termIdIsNullable()) {
            Schema::table('fee_invoices', function (Blueprint $table) {
                $table->foreignId('term_id')->nullable()->change();
            });
        }

        Schema::table('fee_invoices', function (Blueprint $table) {
            // Invoice type — 'school_fee' (default) or 'miscellaneous'
            if (! Schema::hasColumn('fee_invoices', 'invoice_type')) {
                $table->enum('invoice_type', ['school_fee', 'miscellaneous'])
                    ->default('school_fee')
                    ->after('term_id');
            }

            // Description for miscellaneous invoices (e.g. "School Uniform 2026")
            if (! Schema::hasColumn('fee_invoices', 'description')) {
                $table->string('description')->nullable()->after('invoice_type');
            }
        });

        // MySQL was using the old composite unique index to satisfy the
        // student_id foreign key. Create a dedicated student_id index first,
        // then drop the unique constraint.
        if (! $this->hasIndex('fee_invoices', 'fee_invoices_student_id_index')) {
            Schema::table('fee_invoices', function (Blueprint $table) {
                $table->index('student_id', 'fee_invoices_student_id_index');
            });
        }

        if ($this->hasIndex('fee_invoices', 'fee_invoices_student_id_term_id_unique')) {
            DB::statement('ALTER TABLE fee_invoices DROP INDEX fee_invoices_student_id_term_id_unique');
        }
    }

    public function down(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('fee_invoices', 'invoice_type')) {
                $table->dropColumn('invoice_type');
            }

            if (Schema::hasColumn('fee_invoices', 'description')) {
                $table->dropColumn('description');
            }
        });

        if ($this->hasIndex('fee_invoices', 'fee_invoices_student_id_index')) {
            Schema::table('fee_invoices', function (Blueprint $table) {
                $table->dropIndex('fee_invoices_student_id_index');
            });
        }

        if (! $this->hasIndex('fee_invoices', 'fee_invoices_student_id_term_id_unique')) {
            Schema::table('fee_invoices', function (Blueprint $table) {
                $table->unique(['student_id', 'term_id']);
            });
        }

        if ($this->termIdIsNullable()) {
            Schema::table('fee_invoices', function (Blueprint $table) {
                $table->foreignId('term_id')->nullable(false)->change();
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    private function termIdIsNullable(): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.columns')
            ->where('table_schema', $database)
            ->where('table_name', 'fee_invoices')
            ->where('column_name', 'term_id')
            ->value('is_nullable') === 'YES';
    }
};
