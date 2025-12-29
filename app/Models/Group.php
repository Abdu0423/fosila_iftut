<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_id',
        'name',
        'enrollment_year',
        'graduation_year',
        'status',
        'department_id',
        'course',
        'specialty_id'
    ];

    protected $casts = [
        'enrollment_year' => 'integer',
        'graduation_year' => 'integer',
        'course' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'group_student');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function getStatusTextAttribute()
    {
        return [
            'active' => 'Активная',
            'graduated' => 'Выпущена',
            'suspended' => 'Приостановлена'
        ][$this->status] ?? $this->status;
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->enrollment_year}-{$this->graduation_year})";
    }
}
