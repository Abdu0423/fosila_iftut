<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'lesson_id',
        'schedule_id',
        'group_id',
        'teacher_id',
        'due_date',
        'max_points',
        'status',
        'file_path',
        'instructions'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'max_points' => 'integer'
    ];

    /**
     * Отношения
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
