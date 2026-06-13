<?php

namespace App\Observers;

use App\Models\Student;

class StudentObserver
{
    /**
     * Handle the Student "creating" event.
     * Auto-assign roll_number berdasarkan jumlah siswa di kelas
     */
    public function creating(Student $student): void
    {
        if (!$student->roll_number) {
            $highestRollNumber = Student::where('class_room_id', $student->class_room_id)
                ->max('roll_number');
            
            $student->roll_number = ($highestRollNumber ?? 0) + 1;
        }
    }

    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "restored" event.
     */
    public function restored(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "force deleted" event.
     */
    public function forceDeleted(Student $student): void
    {
        //
    }
}
