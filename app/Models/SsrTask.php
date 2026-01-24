<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SsrTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'schedule_id',
        'title',
        'description',
        'requirements',
        'deadline',
        'is_active',
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Учитель, создавший задание
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Расписание (предмет + группа)
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Темы задания
     */
    public function topics()
    {
        return $this->hasMany(SsrTopic::class, 'ssr_task_id');
    }

    /**
     * Работы студентов
     */
    public function submissions()
    {
        return $this->hasMany(SsrSubmission::class, 'ssr_task_id');
    }

    /**
     * Доступные темы (не занятые)
     */
    public function availableTopics()
    {
        return $this->topics()->whereNull('taken_by_student_id');
    }

    /**
     * Занятые темы
     */
    public function takenTopics()
    {
        return $this->topics()->whereNotNull('taken_by_student_id');
    }

    /**
     * Количество доступных тем
     */
    public function getAvailableTopicsCountAttribute()
    {
        return $this->topics()->whereNull('taken_by_student_id')->count();
    }

    /**
     * Количество занятых тем
     */
    public function getTakenTopicsCountAttribute()
    {
        return $this->topics()->whereNotNull('taken_by_student_id')->count();
    }

    /**
     * Общее количество тем
     */
    public function getTotalTopicsCountAttribute()
    {
        return $this->topics()->count();
    }
}
