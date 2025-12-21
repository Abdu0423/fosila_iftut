<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function teachers()
    {
        return $this->hasMany(User::class)->whereHas('role', function($q) {
            $q->where('name', 'teacher');
        });
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
