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
            ['key' => 'dashboard', 'title' => 'Панель', 'exact' => true, 'routes' => [
                'admin' => '/admin',
                'teacher' => '/teacher',
                'education_department' => '/education',
                'registration_center' => '/registration',
                'student' => '/student',
                'department' => '/department',
                'faculty' => '/faculty',
            ]],
            ['key' => 'users', 'title' => 'Пользователи', 'routes' => [
                'admin' => '/admin/users',
                'education_department' => '/admin/users',
                'registration_center' => '/registration/users',
                'department' => '/admin/users',
                'faculty' => '/admin/users',
            ]],
            ['key' => 'schedules', 'title' => 'Расписание', 'routes' => [
                'admin' => '/admin/schedules',
                'education_department' => '/education/schedules',
                'registration_center' => '/registration/schedules',
                'department' => '/department/schedules',
                'faculty' => '/faculty/schedules',
            ]],
            ['key' => 'my_schedule', 'title' => 'Моё расписание', 'routes' => [
                'teacher' => '/teacher/schedule',
                'education_department' => '/education/schedule',
                'registration_center' => '/registration/schedule',
                'department' => '/department/schedule',
                'faculty' => '/faculty/schedule',
            ]],
            ['key' => 'student_schedule', 'title' => 'Расписание студента', 'routes' => [
                'student' => '/student/schedule',
            ]],
            ['key' => 'lessons', 'title' => 'Уроки', 'routes' => [
                'admin' => '/admin/lessons',
                'teacher' => '/teacher/lessons',
                'education_department' => '/education/lessons',
                'registration_center' => '/registration/lessons',
                'department' => '/department/lessons',
                'faculty' => '/faculty/lessons',
            ]],
            ['key' => 'tests', 'title' => 'Тесты', 'routes' => [
                'admin' => '/admin/tests',
                'teacher' => '/teacher/tests',
                'education_department' => '/education/tests',
                'registration_center' => '/registration/tests',
                'student' => '/student/tests',
                'department' => '/department/tests',
                'faculty' => '/faculty/tests',
            ]],
            ['key' => 'ssr', 'title' => 'ССР', 'routes' => [
                'teacher' => '/teacher/ssr',
                'student' => '/student/ssr',
                'department' => '/department/ssr',
                'faculty' => '/faculty/ssr',
            ]],
            ['key' => 'coursework', 'title' => 'Курсовые', 'routes' => [
                'teacher' => '/teacher/coursework',
                'student' => '/student/coursework',
                'department' => '/department/coursework',
                'faculty' => '/faculty/coursework',
            ]],
            ['key' => 'grades', 'title' => 'Оценки', 'routes' => [
                'admin' => '/admin/grades',
                'teacher' => '/teacher/grades',
                'education_department' => '/education/grades',
                'registration_center' => '/registration/grades',
                'student' => '/student/grades',
                'department' => '/department/grades',
                'faculty' => '/faculty/grades',
            ]],
            ['key' => 'students', 'title' => 'Студенты', 'routes' => [
                'teacher' => '/teacher/students',
                'education_department' => '/education/students',
                'registration_center' => '/registration/students',
                'department' => '/department/students',
                'faculty' => '/faculty/students',
            ]],
            ['key' => 'students_management', 'title' => 'Управление студентами', 'routes' => [
                'admin' => '/admin/students',
            ]],
            ['key' => 'transfers', 'title' => 'Переводы', 'routes' => [
                'admin' => '/admin/students/transfers',
            ]],
            ['key' => 'syllabuses', 'title' => 'Силлабусы', 'routes' => [
                'admin' => '/admin/syllabuses',
                'teacher' => '/teacher/syllabuses',
                'education_department' => '/education/syllabuses',
                'registration_center' => '/registration/syllabuses',
                'department' => '/department/syllabuses',
                'faculty' => '/faculty/syllabuses',
            ]],
            ['key' => 'chat', 'title' => 'Чат', 'routes' => [
                'admin' => '/admin/chat',
                'teacher' => '/teacher/chat',
                'education_department' => '/education/chat',
                'registration_center' => '/registration/chat',
                'student' => '/student/chat',
                'department' => '/department/chat',
                'faculty' => '/faculty/chat',
            ]],
            ['key' => 'groups', 'title' => 'Группы', 'routes' => [
                'registration_center' => '/registration/groups',
                'education_department' => '/education/groups',
            ]],
            ['key' => 'subjects', 'title' => 'Предметы', 'routes' => [
                'admin' => '/admin/subjects',
                'registration_center' => '/registration/subjects',
                'education_department' => '/education/subjects',
            ]],
            ['key' => 'catalog_departments', 'title' => 'Кафедры', 'routes' => [
                'admin' => '/admin/departments',
                'registration_center' => '/registration/departments',
                'education_department' => '/education/departments',
            ]],
            ['key' => 'faculties', 'title' => 'Факультеты', 'routes' => [
                'admin' => '/admin/faculties',
            ]],
            ['key' => 'specialties', 'title' => 'Специальности', 'routes' => [
                'registration_center' => '/registration/specialties',
                'education_department' => '/education/specialties',
            ]],
            ['key' => 'assignments', 'title' => 'Задания', 'routes' => [
                'admin' => '/admin/assignments',
                'student' => '/student/assignments',
            ]],
            ['key' => 'library', 'title' => 'Библиотека', 'routes' => [
                'admin' => '/admin/library',
                'student' => '/student/library',
            ]],
            ['key' => 'reports', 'title' => 'Отчёты', 'routes' => [
                'admin' => '/admin/reports',
            ]],
            ['key' => 'sms', 'title' => 'SMS', 'routes' => [
                'admin' => '/admin/sms',
            ]],
            ['key' => 'settings', 'title' => 'Настройки', 'routes' => [
                'admin' => '/admin/settings',
            ]],
        ];
    }

    public static function defaultGrants(): array
    {
        return [
            'teacher' => ['dashboard', 'lessons', 'tests', 'ssr', 'coursework', 'grades', 'students', 'my_schedule', 'syllabuses', 'chat'],
            'education_department' => ['dashboard', 'users', 'schedules', 'lessons', 'tests', 'grades', 'students', 'my_schedule', 'syllabuses', 'chat'],
            'registration_center' => ['dashboard', 'users', 'groups', 'subjects', 'catalog_departments', 'specialties', 'schedules', 'lessons', 'tests', 'grades', 'students', 'my_schedule', 'syllabuses', 'chat'],
            'student' => ['dashboard', 'student_schedule', 'tests', 'ssr', 'coursework', 'assignments', 'chat', 'library', 'grades'],
            'department' => ['dashboard', 'users', 'schedules', 'lessons', 'tests', 'ssr', 'coursework', 'grades', 'students', 'my_schedule', 'syllabuses', 'chat'],
            'faculty' => ['dashboard', 'users', 'schedules', 'lessons', 'tests', 'ssr', 'coursework', 'grades', 'students', 'my_schedule', 'syllabuses', 'chat'],
        ];
    }

    public static function tablesReady(): bool
    {
        return Schema::hasTable('access_pages') && Schema::hasTable('role_page');
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

        return AccessPage::query()
            ->whereHas('roles', fn ($query) => $query->where('roles.id', $user->role_id))
            ->pluck('key')
            ->all();
    }

    public static function allows(?User $user, string $path): bool
    {
        if (! $user || $user->isAdmin() || ! self::tablesReady()) {
            return true;
        }

        $path = self::normalizePath($path);
        $roleName = $user->role?->name;
        if (! $roleName) {
            return false;
        }

        $matchedKey = null;
        $matchedLength = -1;

        foreach (self::definitions() as $page) {
            $route = $page['routes'][$roleName] ?? null;
            if (! $route || ! self::pathMatches($path, $route, (bool) ($page['exact'] ?? false))) {
                continue;
            }

            $length = strlen(rtrim($route, '/'));
            if ($length > $matchedLength) {
                $matchedKey = $page['key'];
                $matchedLength = $length;
            }
        }

        if ($matchedKey === null) {
            return true;
        }

        return in_array($matchedKey, self::grantedKeys($user), true);
    }

    public static function pagesForRole(Role $role): array
    {
        $granted = [];
        if (self::tablesReady()) {
            $granted = AccessPage::query()
                ->whereHas('roles', fn ($query) => $query->where('roles.id', $role->id))
                ->pluck('key')
                ->all();
        }

        $pages = [];
        foreach (self::definitions() as $page) {
            if (empty($page['routes'][$role->name])) {
                continue;
            }
            $pages[] = [
                'key' => $page['key'],
                'title' => $page['title'],
                'enabled' => in_array($page['key'], $granted, true),
            ];
        }

        return $pages;
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
