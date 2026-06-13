<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRoom extends Model
{
    protected $fillable = [
        'name',
        'grade_level',
        'academic_year_id',
        'program_id',
        'section',
        'head_teacher_id',
    ];

    /**
     * Get the academic year for this class room.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the head teacher (wali kelas) for this class room.
     */
    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    /**
     * Get the program for this class room.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get all students in this class room.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get all teaching assignments for this class room.
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /**
     * Get all schedules for this class room.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get formatted full class name
     * Example: "10 MIPA A - Tahun Ajaran 2024/2025"
     */
    public function getFullNameAttribute(): string
    {
        $parts = [$this->grade_level];

        if ($this->program) {
            $parts[] = $this->program->short_name;
        }

        if ($this->section) {
            $parts[] = $this->section;
        }

        return implode(' ', $parts);
    }
}
