<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_id',
        'schedule_id',
        'student_id',
        'rating_teacher_1',
        'rating_teacher_2',
        'rating_test_1',
        'rating_test_2',
        'final_exam_grade',
        'final_exam_type',
        'final_grade_100',
        'final_grade_10',
        'final_grade_letter',
    ];

    protected $casts = [
        'rating_teacher_1' => 'decimal:2',
        'rating_teacher_2' => 'decimal:2',
        'rating_test_1' => 'decimal:2',
        'rating_test_2' => 'decimal:2',
        'final_exam_grade' => 'decimal:2',
        'final_grade_100' => 'decimal:2',
        'final_grade_10' => 'decimal:1',
    ];

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
     * Вычислить итоговую 100-балльную оценку
     */
    public function calculateFinalGrade100()
    {
        $grades = [];
        
        // Рейтинговые оценки учителя (каждая по 20%)
        if ($this->rating_teacher_1 !== null) {
            $grades[] = $this->rating_teacher_1 * 0.20;
        }
        if ($this->rating_teacher_2 !== null) {
            $grades[] = $this->rating_teacher_2 * 0.20;
        }
        
        // Рейтинговые оценки теста (каждая по 20%)
        if ($this->rating_test_1 !== null) {
            $grades[] = $this->rating_test_1 * 0.20;
        }
        if ($this->rating_test_2 !== null) {
            $grades[] = $this->rating_test_2 * 0.20;
        }
        
        // Последний экзамен (20%)
        if ($this->final_exam_grade !== null) {
            $grades[] = $this->final_exam_grade * 0.20;
        }
        
        return count($grades) > 0 ? array_sum($grades) : null;
    }

    /**
     * Обновить итоговые оценки
     */
    public function updateFinalGrades()
    {
        $final100 = $this->calculateFinalGrade100();
        
        if ($final100 !== null) {
            $this->final_grade_100 = $final100;
            $this->final_grade_10 = self::get10PointGrade($final100);
            $this->final_grade_letter = self::getLetterGrade($final100);
            $this->save();
        }
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
