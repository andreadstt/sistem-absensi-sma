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
            // Add head_teacher_id column - nullable because not all classes may have assigned head teacher yet
            $table->foreignId('head_teacher_id')
                ->nullable()
                ->constrained('teachers')
                ->onDelete('set null'); // If teacher is deleted, set to null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['head_teacher_id']);
            $table->dropColumn('head_teacher_id');
        });
    }
};
