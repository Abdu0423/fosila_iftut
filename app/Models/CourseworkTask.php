<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseworkTask extends Model
{
    use HasFactory;

    protected $fillable = [
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
     * Расписание
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Темы курсовой работы
     */
    public function topics()
    {
        return $this->hasMany(CourseworkTopic::class);
    }

    /**
     * Работы студентов
     */
    public function submissions()
    {
        return $this->hasMany(CourseworkSubmission::class);
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
}
