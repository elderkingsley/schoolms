<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parent_credits', function (Blueprint $table) {
            $table->foreignId('student_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('students')
                ->nullOnDelete();

            $table->index(['parent_id', 'student_id', 'status'], 'parent_credits_parent_student_status_index');
        });

        DB::table('parent_credits')
            ->join('fee_invoices', 'fee_invoices.id', '=', 'parent_credits.origin_fee_invoice_id')
            ->whereNull('parent_credits.student_id')
            ->select('parent_credits.id', 'fee_invoices.student_id')
            ->orderBy('parent_credits.id')
            ->chunkById(200, function ($credits) {
                foreach ($credits as $credit) {
                    DB::table('parent_credits')
                        ->where('id', $credit->id)
                        ->update(['student_id' => $credit->student_id]);
                }
            }, 'parent_credits.id', 'id');
    }

    public function down(): void
    {
        Schema::table('parent_credits', function (Blueprint $table) {
            $table->dropIndex('parent_credits_parent_student_status_index');
            $table->dropConstrainedForeignId('student_id');
        });
    }
};
