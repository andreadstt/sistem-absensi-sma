<?php

namespace App\Observers;

use App\Models\Program;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\Attendance;

class ProgramObserver
{
    /**
     * Handle the Program "deleting" event.
     * 
     * Cascade delete all related data when a program is deleted
     */
    public function deleting(Program $program): void
    {
        // Get all class rooms with this program
        $classRoomIds = $program->classRooms()->pluck('id');

        if ($classRoomIds->isNotEmpty()) {
            // Delete all attendances first (has foreign keys to other tables)
            Attendance::whereIn('class_room_id', $classRoomIds)->delete();

            // Delete all schedules
            Schedule::whereIn('class_room_id', $classRoomIds)->delete();

            // Delete all teaching assignments
            TeachingAssignment::whereIn('class_room_id', $classRoomIds)->delete();

            // Delete all students
            Student::whereIn('class_room_id', $classRoomIds)->delete();

            // Finally, delete all class rooms
            ClassRoom::whereIn('id', $classRoomIds)->delete();
        }
    }
}
