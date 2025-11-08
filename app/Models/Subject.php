<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Get all teaching assignments for this subject.
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /**
     * Get all schedules for this subject.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
