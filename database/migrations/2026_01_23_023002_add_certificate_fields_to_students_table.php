<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {

           
            $table->date('course_start_at')->nullable()->after('branch_id');
            $table->date('course_end_at')->nullable()->after('course_start_at');

            $table->timestamp('certificate_issued_at')->nullable()->after('course_end_at');
            $table->string('certificate_number')->nullable()->after('certificate_issued_at');

        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'course_start_at',
                'course_end_at',
                'certificate_issued_at',
                'certificate_number',
            ]);
        });
    }
};
