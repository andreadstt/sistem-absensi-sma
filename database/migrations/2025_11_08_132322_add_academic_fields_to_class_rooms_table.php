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
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('grade_level')->constrained()->nullOnDelete();
            $table->foreignId('program_id')->nullable()->after('academic_year_id')->constrained()->nullOnDelete();
            $table->string('section')->nullable()->after('program_id'); // A, B, C, D

            // Add index for better query performance with shorter name
            $table->index(['academic_year_id', 'grade_level', 'program_id'], 'idx_class_academic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['program_id']);
            $table->dropIndex(['academic_year_id', 'grade_level', 'program_id', 'section']);
            $table->dropColumn(['academic_year_id', 'program_id', 'section']);
        });
    }
};
