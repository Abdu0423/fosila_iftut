<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SsrTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'ssr_task_id',
        'topic_text',
        'taken_by_student_id',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    /**
     * ССР задание
     */
    public function ssrTask()
    {
        return $this->belongsTo(SsrTask::class, 'ssr_task_id');
    }

    /**
     * Студент, выбравший тему
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'taken_by_student_id');
    }

    /**
     * Работа по этой теме
     */
    public function submission()
    {
        return $this->hasOne(SsrSubmission::class, 'topic_id');
    }

    /**
     * Проверить, занята ли тема
     */
    public function isTaken()
    {
        return !is_null($this->taken_by_student_id);
    }

    /**
     * Проверить, доступна ли тема
     */
    public function isAvailable()
    {
        return is_null($this->taken_by_student_id);
    }

    /**
     * Выбрать тему студентом (с защитой от конкурентного доступа)
     * 
     * @param int $studentId
     * @return bool
     * @throws \Exception
     */
    public static function selectTopic(int $topicId, int $studentId): bool
    {
        return DB::transaction(function () use ($topicId, $studentId) {
            // Блокируем строку для обновления
            $topic = self::where('id', $topicId)
                ->whereNull('taken_by_student_id')
                ->lockForUpdate()
                ->first();

            if (!$topic) {
                throw new \Exception('Тема уже выбрана другим студентом или не существует');
            }

            // Проверяем, не выбрал ли студент уже тему в этом задании
            $existingTopic = self::where('ssr_task_id', $topic->ssr_task_id)
                ->where('taken_by_student_id', $studentId)
                ->first();

            if ($existingTopic) {
                throw new \Exception('Вы уже выбрали тему в этом задании');
            }

            $topic->update([
                'taken_by_student_id' => $studentId,
                'taken_at' => now(),
            ]);

            return true;
        });
    }
}
