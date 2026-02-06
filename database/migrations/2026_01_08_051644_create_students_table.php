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
            $table->string('code')->unique();
            $table->string('email', 191)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('name');
            $table->string('status')->default('active'); // status before password
            $table->string('password')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->dateTime('course_start_at')->nullable();
            $table->dateTime('course_end_at')->nullable();
            // $table->dateTime('certificate_issued_at')->nullable();
            // $table->string('certificate_path')->nullable();
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
