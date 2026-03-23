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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_name')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->date('date_of_birth');
            $table->string('photo')->nullable();     // file path in storage
            $table->enum('status', ['active', 'graduated', 'withdrawn'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
