<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'student_id',
        'score',
        'correct_answers',
        'total_questions',
        'is_passed',
        'is_exam',
        'started_at',
        'completed_at',
        'time_spent',
        'answers',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'is_passed' => 'boolean',
        'is_exam' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
