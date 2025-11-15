<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\Grade;
use App\Models\Assignment;
use App\Models\Test;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Общая статистика
        $stats = [
            'users' => User::count(),
            'subjects' => Subject::count(),
            'lessons' => Lesson::count(),
            'activeUsers' => User::where('last_login_at', '>=', Carbon::now()->subDay())->count(),
            'schedules' => Schedule::count(),
            'assignments' => Assignment::count(),
            'tests' => Test::count(),
            'grades' => Grade::count(),
        ];

        // Статистика по ролям
        $roleStats = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->select('roles.name as role_name', DB::raw('count(*) as count'))
            ->groupBy('roles.id', 'roles.name')
            ->get()
            ->map(function($role) {
                return [
                    'name' => $this->getRoleDisplayName($role->role_name),
                    'count' => $role->count,
                    'color' => $this->getRoleColor($role->role_name)
                ];
            });

        // Последние пользователи (последние 10)
        $recentUsers = User::with('role')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? 'Неизвестно',
                    'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'Не указано',
                    'avatar' => $user->avatar ?? "https://ui-avatars.com/api/?name=" . urlencode($user->name)
                ];
            });

        // Последние уроки
        $recentLessons = Lesson::with('subject')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($lesson) {
                // Получаем первое расписание для урока чтобы узнать преподавателя
                $schedule = $lesson->schedules()->with('teacher')->first();
                
                return [
                    'id' => $lesson->id,
                    'name' => $lesson->title ?? 'Без названия',
                    'subject' => optional($lesson->subject)->name ?? 'Без предмета',
                    'teacher' => optional(optional($schedule)->teacher)->name ?? 'Не назначен',
                    'created_at' => $lesson->created_at ? $lesson->created_at->format('Y-m-d H:i:s') : 'Не указано'
                ];
            });

        // Последние оценки
        $recentGrades = Grade::with(['student', 'schedule.subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($grade) {
                return [
                    'id' => $grade->id,
                    'student_name' => optional($grade->student)->name ?? 'Неизвестно',
                    'subject_name' => optional(optional($grade->schedule)->subject)->name ?? 'Неизвестно',
                    'grade' => $grade->grade,
                    'created_at' => $grade->created_at ? $grade->created_at->format('Y-m-d H:i:s') : 'Не указано'
                ];
            });

        // Активность по дням (последние 7 дней)
        $activityStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $activityStats[] = [
                'date' => $date->format('d.m'),
                'users' => User::whereDate('last_login_at', $date->toDateString())->count(),
                'lessons' => Lesson::whereDate('created_at', $date->toDateString())->count(),
                'grades' => Grade::whereDate('created_at', $date->toDateString())->count(),
            ];
        }

        // Статистика по предметам (топ 5)
        $subjectsStats = Subject::withCount(['lessons', 'schedules'])
            ->orderBy('lessons_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'lessons_count' => $subject->lessons_count,
                    'schedules_count' => $subject->schedules_count,
                ];
            });

        // Системные уведомления (можно расширить)
        $systemNotifications = [
            [
                'id' => 1,
                'title' => 'Статистика системы',
                'message' => "В системе зарегистрировано {$stats['users']} пользователей",
                'level' => 'info',
                'created_at' => now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'title' => 'Уроки',
                'message' => "Создано {$stats['lessons']} уроков",
                'level' => 'success',
                'created_at' => now()->subHour()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'title' => 'Активность',
                'message' => "Активных пользователей сегодня: {$stats['activeUsers']}",
                'level' => $stats['activeUsers'] > 10 ? 'success' : 'warning',
                'created_at' => now()->subHours(2)->format('Y-m-d H:i:s')
            ]
        ];

        // Быстрые действия
        $quickActions = [
            [
                'title' => 'Добавить пользователя',
                'icon' => 'mdi-account-plus',
                'route' => '/admin/users/create'
            ],
            [
                'title' => 'Создать урок',
                'icon' => 'mdi-book-plus',
                'route' => '/admin/lessons/create'
            ],
            [
                'title' => 'Создать тест',
                'icon' => 'mdi-help-circle-outline',
                'route' => '/admin/tests/create'
            ],
            [
                'title' => 'Просмотр расписания',
                'icon' => 'mdi-calendar',
                'route' => '/admin/schedules'
            ],
            [
                'title' => 'Просмотр отчетов',
                'icon' => 'mdi-chart-bar',
                'route' => '/admin/reports'
            ],
            [
                'title' => 'Настройки системы',
                'icon' => 'mdi-cog',
                'route' => '/admin/settings'
            ]
        ];

        // Системная информация
        $systemInfo = [
            'version' => '2.0.0',
            'lastUpdate' => now()->format('Y-m-d H:i:s'),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'dbConnection' => config('database.default'),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'roleStats' => $roleStats,
            'recentUsers' => $recentUsers,
            'recentLessons' => $recentLessons,
            'recentGrades' => $recentGrades,
            'activityStats' => $activityStats,
            'subjectsStats' => $subjectsStats,
            'systemNotifications' => $systemNotifications,
            'quickActions' => $quickActions,
            'systemInfo' => $systemInfo,
        ]);
    }

    private function getRoleDisplayName($roleName)
    {
        $names = [
            'admin' => 'Администраторы',
            'teacher' => 'Преподаватели',
            'student' => 'Студенты',
        ];

        return $names[$roleName] ?? $roleName;
    }

    private function getRoleColor($roleName)
    {
        $colors = [
            'admin' => 'error',
            'teacher' => 'warning',
            'student' => 'primary',
        ];

        return $colors[$roleName] ?? 'grey';
    }
}

