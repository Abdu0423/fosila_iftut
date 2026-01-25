<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseworkSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'coursework_task_id',
        'topic_id',
        'student_id',
        'text',
        'file_path',
        'file_name',
        'file_size',
        'teacher_comment',
        'grade_100',
        'grade_10',
        'grade_letter',
        'status',
        'submitted_at',
        'checked_at',
    ];

    protected $casts = [
        'grade_100' => 'decimal:2',
        'grade_10' => 'decimal:1',
        'submitted_at' => 'datetime',
        'checked_at' => 'datetime',
    ];

    /**
     * Курсовая работа
     */
    public function courseworkTask()
    {
        return $this->belongsTo(CourseworkTask::class);
    }

    /**
     * Тема
     */
    public function topic()
    {
        return $this->belongsTo(CourseworkTopic::class, 'topic_id');
    }

    /**
     * Студент
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Получить буквенную оценку на основе 100-балльной
     */
    public static function getLetterGrade($grade100)
    {
        if ($grade100 < 50) return 'F';
        if ($grade100 < 55) return 'D';
        if ($grade100 < 60) return 'D+';
        if ($grade100 < 65) return 'C-';
        if ($grade100 < 70) return 'C';
        if ($grade100 < 75) return 'C+';
        if ($grade100 < 80) return 'B-';
        if ($grade100 < 85) return 'B';
        if ($grade100 < 90) return 'B+';
        if ($grade100 < 95) return 'A-';
        return 'A';
    }

    /**
     * Получить 10-балльную оценку на основе 100-балльной
     */
    public static function get10PointGrade($grade100)
    {
        if ($grade100 < 50) return 0;
        if ($grade100 < 55) return 1;
        if ($grade100 < 60) return 2;
        if ($grade100 < 65) return 3;
        if ($grade100 < 70) return 4;
        if ($grade100 < 75) return 5;
        if ($grade100 < 80) return 6;
        if ($grade100 < 85) return 7;
        if ($grade100 < 90) return 8;
        if ($grade100 < 95) return 9;
        return 10;
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
    public function check($grade100, $comment = null)
    {
        $this->update([
            'grade_100' => $grade100,
            'grade_10' => self::get10PointGrade($grade100),
            'grade_letter' => self::getLetterGrade($grade100),
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
