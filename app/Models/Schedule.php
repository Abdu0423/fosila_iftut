<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'group_id',
        'semester',
        'credits',
        'study_year',
        'order',
        'scheduled_at',
        'is_active'
    ];

    protected $casts = [
        'semester' => 'integer',
        'credits' => 'integer',
        'study_year' => 'integer',
        'order' => 'integer',
        'scheduled_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'display_name'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Отношение к силлабусам (many-to-many)
     */
    public function syllabuses()
    {
        return $this->belongsToMany(Syllabus::class, 'schedule_syllabus')
                    ->withTimestamps();
    }

    /**
     * Отношение к урокам (many-to-many)
     */
    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_schedule')
                    ->withPivot(['order', 'duration', 'start_time', 'end_time', 'room', 'notes'])
                    ->withTimestamps()
                    ->orderBy('lesson_schedule.order');
    }

    /**
     * Отношение к тесту (one-to-one) - для обратной совместимости
     */
    public function test()
    {
        return $this->hasOne(Test::class);
    }

    /**
     * Отношение к тестам (one-to-many) - для подсчета количества
     */
    public function tests()
    {
        return $this->hasMany(Test::class);
    }

    /**
     * Отношение к оценкам (one-to-many)
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Получить отображаемое имя расписания
     */
    public function getDisplayNameAttribute()
    {
        return $this->subject ? $this->subject->name : 'Расписание #' . $this->id;
    }
}
