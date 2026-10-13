<?php

namespace App\Support;

use App\Models\AccessPage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PageAccess
{
    public static function definitions(): array
    {
        return [
            ['key' => 'dashboard', 'title' => 'Панель', 'audience' => 'staff', 'exact' => true, 'route' => '/admin', 'aliases' => [
                '/teacher', '/education', '/education/dashboard', '/registration', '/registration/dashboard', '/department', '/faculty',
            ]],
            ['key' => 'users', 'title' => 'Пользователи', 'audience' => 'staff', 'route' => '/admin/users', 'aliases' => [
                '/education/users', '/registration/users',
            ]],
            ['key' => 'schedules', 'title' => 'Расписание', 'audience' => 'staff', 'route' => '/admin/schedules', 'aliases' => [
                '/education/schedules', '/registration/schedules', '/department/schedules', '/faculty/schedules',
            ]],
            ['key' => 'my_schedule', 'title' => 'Моё расписание', 'audience' => 'staff', 'route' => '/teacher/schedule', 'aliases' => [
                '/education/schedule', '/registration/schedule', '/department/schedule', '/faculty/schedule',
            ]],
            ['key' => 'lessons', 'title' => 'Уроки', 'audience' => 'staff', 'route' => '/teacher/lessons', 'aliases' => [
                '/admin/lessons', '/education/lessons', '/registration/lessons', '/department/lessons', '/faculty/lessons',
            ]],
            ['key' => 'tests', 'title' => 'Тесты', 'audience' => 'staff', 'route' => '/teacher/tests', 'aliases' => [
                '/admin/tests', '/education/tests', '/registration/tests', '/department/tests', '/faculty/tests',
            ]],
            ['key' => 'ssr', 'title' => 'ССР', 'audience' => 'staff', 'route' => '/teacher/ssr', 'aliases' => [
                '/education/ssr', '/registration/ssr', '/department/ssr', '/faculty/ssr',
            ]],
            ['key' => 'coursework', 'title' => 'Курсовые', 'audience' => 'staff', 'route' => '/teacher/coursework', 'aliases' => [
                '/education/coursework', '/registration/coursework', '/department/coursework', '/faculty/coursework',
            ]],
            ['key' => 'grades', 'title' => 'Оценки', 'audience' => 'staff', 'route' => '/teacher/grades', 'aliases' => [
                '/admin/grades', '/education/grades', '/registration/grades', '/department/grades', '/faculty/grades',
            ]],
            ['key' => 'students', 'title' => 'Студенты', 'audience' => 'staff', 'route' => '/teacher/students', 'aliases' => [
                '/education/students', '/registration/students', '/department/students', '/faculty/students',
            ]],
            ['key' => 'students_management', 'title' => 'Управление студентами', 'audience' => 'staff', 'route' => '/admin/students'],
            ['key' => 'transfers', 'title' => 'Переводы', 'audience' => 'staff', 'route' => '/admin/students/transfers'],
            ['key' => 'syllabuses', 'title' => 'Силлабусы', 'audience' => 'staff', 'route' => '/teacher/syllabuses', 'aliases' => [
                '/admin/syllabuses', '/education/syllabuses', '/registration/syllabuses', '/department/syllabuses', '/faculty/syllabuses',
            ]],
            ['key' => 'chat', 'title' => 'Чат', 'audience' => 'staff', 'route' => '/teacher/chat', 'aliases' => [
                '/admin/chat', '/education/chat', '/registration/chat', '/department/chat', '/faculty/chat',
            ]],
            ['key' => 'groups', 'title' => 'Группы', 'audience' => 'staff', 'route' => '/education/groups', 'aliases' => [
                '/registration/groups',
            ]],
            ['key' => 'subjects', 'title' => 'Предметы', 'audience' => 'staff', 'route' => '/admin/subjects', 'aliases' => [
                '/education/subjects', '/registration/subjects',
            ]],
            ['key' => 'catalog_departments', 'title' => 'Кафедры', 'audience' => 'staff', 'route' => '/admin/departments', 'aliases' => [
                '/education/departments', '/registration/departments',
            ]],
            ['key' => 'faculties', 'title' => 'Факультеты', 'audience' => 'staff', 'route' => '/admin/faculties'],
            ['key' => 'specialties', 'title' => 'Специальности', 'audience' => 'staff', 'route' => '/education/specialties', 'aliases' => [
                '/registration/specialties',
            ]],
            ['key' => 'assignments', 'title' => 'Задания', 'audience' => 'staff', 'route' => '/admin/assignments'],
            ['key' => 'library', 'title' => 'Библиотека', 'audience' => 'staff', 'route' => '/admin/library'],
            ['key' => 'reports', 'title' => 'Отчёты', 'audience' => 'staff', 'route' => '/admin/reports', 'aliases' => [
                '/teacher/reports', '/education/reports', '/registration/reports',
            ]],
            ['key' => 'sms', 'title' => 'SMS', 'audience' => 'staff', 'route' => '/admin/sms'],
            ['key' => 'settings', 'title' => 'Настройки', 'audience' => 'staff', 'route' => '/admin/settings'],

            ['key' => 'student_dashboard', 'title' => 'Панель', 'audience' => 'student', 'exact' => true, 'route' => '/student', 'aliases' => [
                '/student/dashboard',
            ]],
            ['key' => 'student_schedule', 'title' => 'Расписание', 'audience' => 'student', 'route' => '/student/schedule'],
            ['key' => 'student_tests', 'title' => 'Тесты', 'audience' => 'student', 'route' => '/student/tests'],
            ['key' => 'student_ssr', 'title' => 'ССР', 'audience' => 'student', 'route' => '/student/ssr'],
            ['key' => 'student_coursework', 'title' => 'Курсовые', 'audience' => 'student', 'route' => '/student/coursework'],
            ['key' => 'student_grades', 'title' => 'Оценки', 'audience' => 'student', 'route' => '/student/grades'],
            ['key' => 'student_assignments', 'title' => 'Задания', 'audience' => 'student', 'route' => '/student/assignments'],
            ['key' => 'student_chat', 'title' => 'Чат', 'audience' => 'student', 'route' => '/student/chat'],
            ['key' => 'student_library', 'title' => 'Библиотека', 'audience' => 'student', 'route' => '/student/library'],
        ];
    }

    public static function defaultGrants(): array
    {
        $teacherPages = ['dashboard', 'lessons', 'tests', 'ssr', 'coursework', 'grades', 'students', 'my_schedule', 'syllabuses', 'chat'];

        return [
            'teacher' => $teacherPages,
            'education_department' => array_values(array_unique(array_merge(
                $teacherPages,
                ['users', 'schedules']
            ))),
            'registration_center' => array_values(array_unique(array_merge(
                $teacherPages,
                ['users', 'groups', 'subjects', 'catalog_departments', 'specialties', 'schedules']
            ))),
            'department' => array_values(array_unique(array_merge(
                $teacherPages,
                ['users', 'schedules']
            ))),
            'faculty' => array_values(array_unique(array_merge(
                $teacherPages,
                ['users', 'schedules']
            ))),
            'student' => [
                'student_dashboard',
                'student_schedule',
                'student_tests',
                'student_ssr',
                'student_coursework',
                'student_assignments',
                'student_chat',
                'student_library',
                'student_grades',
            ],
        ];
    }

    public static function tablesReady(): bool
    {
        return Schema::hasTable('access_pages') && Schema::hasTable('role_page');
    }

    public static function audienceFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $user->isStudent() ? 'student' : 'staff';
    }

    public static function keysForAudience(string $audience): array
    {
        return collect(self::definitions())
            ->filter(fn ($page) => ($page['audience'] ?? 'staff') === $audience)
            ->pluck('key')
            ->all();
    }

    public static function grantedKeys(?User $user): ?array
    {
        if (! $user || $user->isAdmin() || ! self::tablesReady()) {
            return null;
        }

        $roleName = $user->role?->name;
        if (! $roleName) {
            return [];
        }

        $audience = self::audienceFor($user);

        $keys = AccessPage::query()
            ->whereHas('roles', fn ($query) => $query->where('roles.id', $user->role_id))
            ->pluck('key')
            ->all();

        return self::expandKeys($keys, $audience);
    }

    public static function allows(?User $user, string $path): bool
    {
        if (! $user || ! self::tablesReady()) {
            return true;
        }

        $path = self::normalizePath($path);
        $matched = self::matchPage($path);

        if ($user->isAdmin()) {
            return ! $matched || ($matched['audience'] ?? 'staff') !== 'student';
        }

        if (! $matched) {
            return true;
        }

        if (($matched['audience'] ?? 'staff') !== self::audienceFor($user)) {
            return false;
        }

        if (in_array($matched['key'], ['dashboard', 'student_dashboard'], true) && ! self::isOwnHome($user, $path)) {
            return false;
        }

        return in_array($matched['key'], self::grantedKeys($user) ?? [], true);
    }

    public static function pagesForRole(Role $role): array
    {
        $audience = $role->name === 'student' ? 'student' : 'staff';
        $granted = [];
        if (self::tablesReady()) {
            $granted = self::expandKeys(
                AccessPage::query()
                    ->whereHas('roles', fn ($query) => $query->where('roles.id', $role->id))
                    ->pluck('key')
                    ->all(),
                $audience
            );
        }

        $pages = [];
        foreach (self::definitions() as $page) {
            if (($page['audience'] ?? 'staff') !== $audience) {
                continue;
            }
            $pages[] = [
                'key' => $page['key'],
                'title' => $page['title'],
                'route' => $page['route'],
                'enabled' => in_array($page['key'], $granted, true),
            ];
        }

        return $pages;
    }

    private static function isOwnHome(?User $user, string $path): bool
    {
        $homes = [
            'admin' => '/admin',
            'teacher' => '/teacher',
            'education_department' => '/education',
            'registration_center' => '/registration',
            'department' => '/department',
            'faculty' => '/faculty',
            'student' => '/student',
        ];
        $home = $homes[$user?->role?->name ?? ''] ?? null;
        if (! $home) {
            return false;
        }

        return $path === $home || $path === $home.'/dashboard';
    }

    private static function expandKeys(array $keys, string $audience): array
    {
        $legacyStudent = [
            'dashboard' => 'student_dashboard',
            'tests' => 'student_tests',
            'ssr' => 'student_ssr',
            'coursework' => 'student_coursework',
            'grades' => 'student_grades',
            'chat' => 'student_chat',
            'assignments' => 'student_assignments',
            'library' => 'student_library',
        ];

        $allowed = self::keysForAudience($audience);

        return collect($keys)
            ->map(fn ($key) => $audience === 'student' ? ($legacyStudent[$key] ?? $key) : $key)
            ->filter(fn ($key) => in_array($key, $allowed, true))
            ->unique()
            ->values()
            ->all();
    }

    public static function normalizePath(string $path): string
    {
        $path = '/'.trim($path, '/');
        if (str_starts_with($path, '/api/')) {
            $path = substr($path, 4) ?: '/';
        } elseif ($path === '/api') {
            $path = '/';
        }

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private static function matchPage(string $path): ?array
    {
        $matched = null;
        $matchedLength = -1;

        foreach (self::definitions() as $page) {
            $paths = array_merge([$page['route']], $page['aliases'] ?? []);
            foreach ($paths as $route) {
                if (! self::pathMatches($path, $route, (bool) ($page['exact'] ?? false))) {
                    continue;
                }

                $length = strlen(rtrim($route, '/'));
                if ($length > $matchedLength) {
                    $matched = $page;
                    $matchedLength = $length;
                }
            }
        }

        return $matched;
    }

    private static function pathMatches(string $path, string $route, bool $exact): bool
    {
        $path = $path === '/' ? '/' : rtrim($path, '/');
        $route = rtrim($route, '/') ?: '/';

        if ($exact) {
            return $path === $route;
        }

        return $path === $route || str_starts_with($path, $route.'/');
    }
}
