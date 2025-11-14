<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'class_room_id',
        'subject_id',
        'teacher_id',
        'weekday',
        'time_slot',
    ];

    /**
     * Boot method to auto-create TeachingAssignment when Schedule is created.
     */
    protected static function booted(): void
    {
        static::creating(function (Schedule $schedule) {
            // Auto-create TeachingAssignment if not exists
            TeachingAssignment::firstOrCreate([
                'teacher_id' => $schedule->teacher_id,
                'subject_id' => $schedule->subject_id,
                'class_room_id' => $schedule->class_room_id,
            ]);
        });

        static::updating(function (Schedule $schedule) {
            // If schedule is updated, ensure TeachingAssignment exists for new combination
            TeachingAssignment::firstOrCreate([
                'teacher_id' => $schedule->teacher_id,
                'subject_id' => $schedule->subject_id,
                'class_room_id' => $schedule->class_room_id,
            ]);
        });
    }

    /**
     * Get the teacher for this schedule.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the subject for this schedule.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class room for this schedule.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
