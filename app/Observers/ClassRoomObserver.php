<?php

namespace App\Observers;

use App\Models\ClassRoom;

class ClassRoomObserver
{
    /**
     * Handle the ClassRoom "creating" event.
     */
    public function creating(ClassRoom $classRoom): void
    {
        $this->setAutoName($classRoom);
    }

    /**
     * Handle the ClassRoom "updating" event.
     */
    public function updating(ClassRoom $classRoom): void
    {
        $this->setAutoName($classRoom);
    }

    /**
     * Set auto-generated name if not provided
     */
    private function setAutoName(ClassRoom $classRoom): void
    {
        // Only auto-generate if name is empty
        if (empty($classRoom->name)) {
            $parts = [];

            if ($classRoom->grade_level) {
                $parts[] = $classRoom->grade_level;
            }

            if ($classRoom->program_id) {
                $program = \App\Models\Program::find($classRoom->program_id);
                if ($program) {
                    $parts[] = $program->short_name;
                }
            }

            if ($classRoom->section) {
                $parts[] = $classRoom->section;
            }

            $classRoom->name = implode(' ', $parts);
        }
    }
}
