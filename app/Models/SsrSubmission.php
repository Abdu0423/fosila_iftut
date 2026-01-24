<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SsrSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'ssr_task_id',
        'topic_id',
        'student_id',
        'text',
        'file_path',
        'file_name',
        'file_size',
        'teacher_comment',
        'grade',
        'status',
        'submitted_at',
        'checked_at',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'submitted_at' => 'datetime',
        'checked_at' => 'datetime',
    ];

    /**
     * ССР задание
     */
    public function ssrTask()
    {
        return $this->belongsTo(SsrTask::class, 'ssr_task_id');
    }

    /**
     * Тема
     */
    public function topic()
    {
        return $this->belongsTo(SsrTopic::class, 'topic_id');
    }

    /**
     * Студент
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Проверено ли задание
     */
    public function isChecked()
    {
        return $this->status === 'checked';
    }

    /**
     * Отправлено ли задание
     */
    public function isSubmitted()
    {
        return in_array($this->status, ['submitted', 'checked', 'returned']);
    }

    /**
     * Черновик
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Возвращено на доработку
     */
    public function isReturned()
    {
        return $this->status === 'returned';
    }

    /**
     * Отправить работу
     */
    public function submit()
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Проверить работу (учитель)
     */
    public function check($grade, $comment = null)
    {
        $this->update([
            'grade' => $grade,
            'teacher_comment' => $comment,
            'status' => 'checked',
            'checked_at' => now(),
        ]);
    }

    /**
     * Вернуть на доработку
     */
    public function returnForRevision($comment)
    {
        $this->update([
            'teacher_comment' => $comment,
            'status' => 'returned',
        ]);
    }

    /**
     * Получить размер файла в читаемом формате
     */
    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
