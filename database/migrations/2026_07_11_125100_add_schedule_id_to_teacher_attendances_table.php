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
        Schema::table('teacher_attendances', function (Blueprint $table) {
            // 1. Drop foreign key on teacher_id first (it depends on the unique index)
            $table->dropForeign(['teacher_id']);

            // 2. Now safe to drop the old unique index
            $table->dropUnique(['teacher_id', 'date']);
        });

        Schema::table('teacher_attendances', function (Blueprint $table) {
            // 3. Add schedule_id column with foreign key
            $table->foreignId('schedule_id')
                ->after('teacher_id')
                ->constrained('schedules')
                ->onDelete('cascade');

            // 4. Add new unique constraint
            $table->unique(['teacher_id', 'schedule_id', 'date']);

            // 5. Re-add foreign key on teacher_id
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_attendances', function (Blueprint $table) {
            // Drop the re-added teacher_id FK
            $table->dropForeign(['teacher_id']);

            // Drop new unique constraint
            $table->dropUnique(['teacher_id', 'schedule_id', 'date']);

            // Drop schedule_id column (also drops its foreign key)
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
        });

        Schema::table('teacher_attendances', function (Blueprint $table) {
            // Restore old unique constraint
            $table->unique(['teacher_id', 'date']);

            // Restore original teacher_id foreign key
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
        });
    }
};

