<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, find and nullify duplicate head_teacher_id entries.
        $duplicateTeacherIds = DB::table('class_rooms')
            ->select('head_teacher_id')
            ->whereNotNull('head_teacher_id')
            ->groupBy('head_teacher_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('head_teacher_id');

        foreach ($duplicateTeacherIds as $teacherId) {
            // Get all class IDs for the duplicate teacher, ordered by creation time or ID.
            $classRoomIds = DB::table('class_rooms')
                ->where('head_teacher_id', $teacherId)
                ->orderBy('id')
                ->pluck('id');

            // Keep the first one, and nullify the rest.
            $idsToNullify = $classRoomIds->slice(1);

            if ($idsToNullify->isNotEmpty()) {
                DB::table('class_rooms')
                    ->whereIn('id', $idsToNullify)
                    ->update(['head_teacher_id' => null]);
            }
        }

        // Now, with clean data, add the unique constraint.
        Schema::table('class_rooms', function (Blueprint $table) {
            // Make the column nullable if it isn't already, and then add the unique constraint.
            // A unique constraint on a nullable column allows multiple null values.
            $table->unique('head_teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->dropUnique('class_rooms_head_teacher_id_unique');
        });
    }
};
