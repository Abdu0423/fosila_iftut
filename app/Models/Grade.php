<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'student_id',
        'grade_1',
        'grade_2',
        'grade_3',
        'grade_4',
        'grade_5',
    ];

    protected $casts = [
        'grade_1' => 'decimal:2',
        'grade_2' => 'decimal:2',
        'grade_3' => 'decimal:2',
        'grade_4' => 'decimal:2',
        'grade_5' => 'decimal:2',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
