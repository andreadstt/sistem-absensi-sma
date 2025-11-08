<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingAssignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_room_id',
    ];

    /**
     * Get the teacher for this assignment.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the subject for this assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class room for this assignment.
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    /**
     * Boot method to enforce business rule: max 3 subjects per teacher.
     */
    protected static function booted(): void
    {
        static::creating(function (TeachingAssignment $assignment) {
            $subjectCount = static::where('teacher_id', $assignment->teacher_id)
                ->distinct('subject_id')
                ->count('subject_id');

            if ($subjectCount >= 3) {
                throw new \Exception('A teacher cannot have more than 3 subjects.');
            }
        });
    }
}
