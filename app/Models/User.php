<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'middle_name',
        'email',
        'password',
        'role_id',
        'group_id',
        'address',
        'phone',
        'dad_phone',
        'mom_phone',
        'email_verified_at',
        'last_login_at',
        'must_change_password',
        'locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_student');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function isAdmin()
    {
        // Если роль не загружена, загружаем её
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->name === 'admin';
    }

    public function isTeacher()
    {
        // Если роль не загружена, загружаем её
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->name === 'teacher';
    }

    public function isStudent()
    {
        // Если роль не загружена, загружаем её
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->name === 'student';
    }

    /**
     * Чаты пользователя
     */
    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_user')
                    ->withPivot(['role', 'joined_at', 'last_read_at', 'is_muted', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Сообщения пользователя
     */
    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Получить полное имя пользователя
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([$this->last_name, $this->name, $this->middle_name]);
        return implode(' ', $parts) ?: $this->name;
    }

    /**
     * Получить инициалы пользователя
     */
    public function getInitialsAttribute()
    {
        $initials = '';
        if ($this->last_name) $initials .= mb_substr($this->last_name, 0, 1);
        if ($this->name) $initials .= mb_substr($this->name, 0, 1);
        return mb_strtoupper($initials);
    }
}
