<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_id',
        'department_id',
        'name',
        'code',
        'content',
        'description',
        'credits',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credits' => 'integer',
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
