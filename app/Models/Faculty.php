<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'dean_name',
        'dean_email',
        'phone',
        'address',
        'user_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teachers()
    {
        return $this->hasManyThrough(User::class, Department::class)
            ->whereHas('role', function($q) {
                $q->where('name', 'teacher');
            });
    }

    public function students()
    {
        return $this->hasManyThrough(User::class, Department::class)
            ->whereHas('role', function($q) {
                $q->where('name', 'student');
            });
    }
}
