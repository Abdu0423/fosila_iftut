<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

class ChangeLog
{
    public const SECRET = 'скрыто';

    private const EXCLUDED = [
        'change_logs',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'failed_jobs',
        'migrations',
    ];

    public const TABLES = [
        'users' => 'Пользователи',
        'roles' => 'Роли',
        'faculties' => 'Факультеты',
        'departments' => 'Кафедры',
        'specialties' => 'Специальности',
        'groups' => 'Группы',
        'group_student' => 'Студенты группы',
        'subjects' => 'Предметы',
        'lessons' => 'Уроки',
        'lesson_schedule' => 'Уроки расписания',
        'schedules' => 'Расписания',
        'syllabuses' => 'Силлабусы',
        'schedule_syllabus' => 'Силлабусы расписания',
        'tests' => 'Тесты',
        'test_questions' => 'Вопросы теста',
        'test_answers' => 'Ответы теста',
        'test_attempts' => 'Попытки теста',
        'grades' => 'Оценки',
        'assignments' => 'Задания',
        'submissions' => 'Сдачи заданий',
        'courses' => 'Курсы',
        'settings' => 'Настройки',
        'chats' => 'Чаты',
        'chat_messages' => 'Сообщения чата',
        'chat_user' => 'Участники чата',
        'sms_messages' => 'SMS',
        'ssr_tasks' => 'Задания ССР',
        'ssr_topics' => 'Темы ССР',
        'ssr_submissions' => 'Сдачи ССР',
        'coursework_tasks' => 'Задания курсовых',
        'coursework_topics' => 'Темы курсовых',
        'coursework_submissions' => 'Сдачи курсовых',
        'access_pages' => 'Страницы доступа',
        'role_page' => 'Доступ роли',
        'password_reset_codes' => 'Коды сброса пароля',
        'password_reset_tokens' => 'Токены сброса пароля',
        'personal_access_tokens' => 'Токены доступа',
    ];

    public const ACTIONS = [
        'created' => 'Добавление',
        'updated' => 'Изменение',
        'deleted' => 'Удаление',
    ];

    public const ROLES = [
        'admin' => 'Администратор',
        'teacher' => 'Учитель',
        'education_department' => 'Образование',
        'registration_center' => 'Регистрация',
        'department' => 'Кафедра',
        'faculty' => 'Факультет',
        'student' => 'Студент',
    ];

    private static bool $registered = false;

    private static ?bool $ready = null;

    private static bool $writing = false;

    private static ?string $queryTable = null;

    /** @var array<string, int> */
    private static array $skip = [];

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        foreach (['creating', 'updating', 'deleting'] as $event) {
            Event::listen('eloquent.'.$event.': *', function ($name, $payload) {
                $model = self::modelFrom($payload);
                if ($model && ! self::excluded($model->getTable())) {
                    self::$skip[$model->getTable()] = (self::$skip[$model->getTable()] ?? 0) + 1;
                }
            });
        }

        Event::listen('eloquent.created: *', function ($name, $payload) {
            $model = self::modelFrom($payload);
            if (! $model) {
                return;
            }
            self::writeModel('created', $model, null, self::attributesOf($model, $model->getAttributes()));
        });

        Event::listen('eloquent.updated: *', function ($name, $payload) {
            $model = self::modelFrom($payload);
            if (! $model) {
                return;
            }
            $changes = $model->getChanges();
            if ($changes === []) {
                return;
            }
            $before = [];
            $after = [];
            self::during($model->getTable(), function () use ($model, $changes, &$before, &$after) {
                foreach ($changes as $key => $value) {
                    $before[$key] = self::plain($model->getOriginal($key), $key);
                    $after[$key] = self::plain($value, $key);
                }
            });
            self::writeModel('updated', $model, $before, $after);
        });

        Event::listen('eloquent.deleted: *', function ($name, $payload) {
            $model = self::modelFrom($payload);
            if (! $model) {
                return;
            }
            $before = self::attributesOf($model, $model->getOriginal() ?: $model->getAttributes());
            self::writeModel('deleted', $model, $before, null);
        });

        DB::listen(function ($query) {
            if (self::$writing) {
                return;
            }
            $inspected = self::inspectQuery($query->sql, $query->bindings);
            if (! $inspected) {
                return;
            }
            $table = $inspected['table'];
            if ((self::$skip[$table] ?? 0) > 0) {
                self::$skip[$table]--;

                return;
            }
            self::store(
                $inspected['action'],
                $table,
                $inspected['record_id'],
                $inspected['before'],
                $inspected['after']
            );
        });
    }

    public static function inspectQuery(string $sql, array $bindings): ?array
    {
        if (! preg_match('/^\s*(insert|update|delete)\b/i', $sql, $actionMatch)) {
            return null;
        }
        $verb = strtolower($actionMatch[1]);
        $table = self::tableFromSql($sql, $verb);
        if (! $table || self::excluded($table)) {
            return null;
        }

        $previousTable = self::$queryTable;
        self::$queryTable = $table;
        try {
            $bindings = array_map(fn ($value) => self::plain($value), array_values($bindings));
            $action = $verb === 'insert' ? 'created' : ($verb === 'update' ? 'updated' : 'deleted');

            if ($verb === 'insert' && preg_match('/\(([^)]+)\)\s*values\s*(.+)$/is', $sql, $insert)) {
                $columns = self::columnList($insert[1]);
                $rows = self::valueRows($insert[2], $bindings);
                if ($columns && $rows) {
                    $mapped = array_values(array_filter(array_map(fn ($row) => self::combine($columns, $row), $rows)));
                    if ($mapped) {
                        $after = count($mapped) === 1 ? $mapped[0] : $mapped;

                        return [
                            'action' => $action,
                            'table' => $table,
                            'record_id' => count($mapped) === 1 ? self::recordId($mapped[0]) : null,
                            'before' => null,
                            'after' => $after,
                        ];
                    }
                }
            }

            $wherePos = stripos($sql, ' where ');
            $head = $wherePos === false ? $sql : substr($sql, 0, $wherePos);
            $where = $wherePos === false ? '' : substr($sql, $wherePos);
            $set = self::consume(self::assignments($head), $bindings, 0);
            $conditions = self::consume(self::assignments($where), $bindings, $set['used']);

            if ($set['ok'] && $conditions['ok'] && ($set['used'] + $conditions['used']) > 0) {
                return [
                    'action' => $action,
                    'table' => $table,
                    'record_id' => self::recordId($conditions['data']) ?? self::recordId($set['data']),
                    'before' => $verb === 'insert' ? null : ($conditions['data'] ?: null),
                    'after' => $verb === 'delete' ? null : ($set['data'] ?: null),
                ];
            }

            return [
                'action' => $action,
                'table' => $table,
                'record_id' => null,
                'before' => $verb === 'delete' ? ['_values' => $bindings] : null,
                'after' => $verb === 'delete' ? null : ['_values' => $bindings],
            ];
        } finally {
            self::$queryTable = $previousTable;
        }
    }

    public static function tableLabel(string $table): string
    {
        return self::TABLES[$table] ?? $table;
    }

    public static function actionLabel(string $action): string
    {
        return self::ACTIONS[$action] ?? $action;
    }

    public static function roleLabel(?string $role): string
    {
        if (! $role) {
            return '';
        }

        return self::ROLES[$role] ?? $role;
    }

    private static function attributesOf(Model $model, array $attributes): array
    {
        return self::during($model->getTable(), fn () => self::plain($attributes));
    }

    private static function during(string $table, callable $callback)
    {
        $previous = self::$queryTable;
        self::$queryTable = $table;
        try {
            return $callback();
        } finally {
            self::$queryTable = $previous;
        }
    }

    private static function writeModel(string $action, Model $model, ?array $before, ?array $after): void
    {
        if (self::excluded($model->getTable())) {
            return;
        }
        $key = $model->getKey();
        self::store($action, $model->getTable(), $key === null ? null : (string) $key, $before, $after);
    }

    private static function store(string $action, string $table, ?string $recordId, ?array $before, ?array $after): void
    {
        if (self::$writing || ! self::tableReady()) {
            return;
        }

        $user = Auth::user();
        $role = $user?->role?->name;
        self::$writing = true;
        try {
            DB::table('change_logs')->insert([
                'user_id' => $user?->id,
                'user_name' => $user ? ($user->full_name ?: $user->name) : null,
                'role_name' => $role,
                'action' => $action,
                'table_name' => $table,
                'record_id' => $recordId,
                'before' => $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE),
                'after' => $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE),
                'url' => request()?->getRequestUri(),
                'created_at' => now(),
            ]);
        } finally {
            self::$writing = false;
        }
    }

    private static function tableReady(): bool
    {
        if (self::$ready === true) {
            return true;
        }
        self::$ready = Schema::hasTable('change_logs');

        return self::$ready;
    }

    private static function modelFrom($payload): ?Model
    {
        $model = is_array($payload) ? ($payload[0] ?? null) : $payload;

        return $model instanceof Model ? $model : null;
    }

    private static function excluded(string $table): bool
    {
        return in_array($table, self::EXCLUDED, true);
    }

    private static function tableFromSql(string $sql, string $verb): ?string
    {
        $pattern = $verb === 'update'
            ? '/update\s+(?:`?[a-z0-9_]+`?\.)?`?([a-z0-9_]+)`?/i'
            : ($verb === 'insert'
                ? '/insert\s+into\s+(?:`?[a-z0-9_]+`?\.)?`?([a-z0-9_]+)`?/i'
                : '/delete\s+from\s+(?:`?[a-z0-9_]+`?\.)?`?([a-z0-9_]+)`?/i');

        return preg_match($pattern, $sql, $match) ? $match[1] : null;
    }

    private static function columnList(string $sql): array
    {
        preg_match_all('/`?([a-z0-9_]+)`?/i', $sql, $match);

        return $match[1] ?? [];
    }

    private static function valueRows(string $sql, array $bindings): array
    {
        preg_match_all('/\(([^)]*)\)/', $sql, $groups);
        if (! $groups[1]) {
            return [];
        }
        $rows = [];
        $offset = 0;
        foreach ($groups[1] as $group) {
            $count = substr_count($group, '?');
            if ($count === 0 || $offset + $count > count($bindings)) {
                return [];
            }
            $rows[] = array_slice($bindings, $offset, $count);
            $offset += $count;
        }

        return $offset === count($bindings) ? $rows : [];
    }

    private static function assignments(string $sql): array
    {
        preg_match_all('/`(?:[a-z0-9_]+\.)?([a-z0-9_]+)`\s*(?:=|in)\s*(\((?:\s*\?\s*,)*\s*\?\s*\)|\?)/i', $sql, $matches, PREG_SET_ORDER);
        $items = [];
        foreach ($matches as $match) {
            $items[] = [
                'column' => $match[1],
                'count' => substr_count($match[2], '?'),
            ];
        }

        return $items;
    }

    private static function consume(array $items, array $bindings, int $offset): array
    {
        $data = [];
        $used = 0;
        foreach ($items as $item) {
            $count = $item['count'];
            if ($offset + $used + $count > count($bindings)) {
                return ['data' => [], 'used' => 0, 'ok' => false];
            }
            $slice = array_slice($bindings, $offset + $used, $count);
            $value = $count === 1 ? $slice[0] : $slice;
            $data[$item['column']] = self::plain($value, $item['column']);
            $used += $count;
        }

        return ['data' => $data, 'used' => $used, 'ok' => true];
    }

    private static function combine(array $columns, array $values): array
    {
        if (count($columns) !== count($values)) {
            return [];
        }
        $data = [];
        foreach ($columns as $index => $column) {
            $data[$column] = self::plain($values[$index], $column);
        }

        return $data;
    }

    private static function recordId(array $data): ?string
    {
        if (! array_key_exists('id', $data) || is_array($data['id']) || $data['id'] === null) {
            return null;
        }

        return (string) $data['id'];
    }

    private static function plain($value, ?string $key = null)
    {
        if ($key && self::secretKey($key)) {
            return self::SECRET;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_array($value)) {
            $clean = [];
            foreach ($value as $childKey => $child) {
                $clean[$childKey] = self::plain($child, is_string($childKey) ? $childKey : null);
            }

            return $clean;
        }
        if (is_bool($value) || is_int($value) || is_float($value) || $value === null || is_string($value)) {
            return $value;
        }

        return (string) $value;
    }

    private static function secretKey(string $key): bool
    {
        $key = strtolower($key);
        if (in_array($key, ['password', 'remember_token', 'token', 'api_token', 'secret'], true)) {
            return true;
        }
        if (str_ends_with($key, '_token') || str_ends_with($key, '_secret')) {
            return true;
        }
        if (str_contains($key, 'password') && ! str_starts_with($key, 'must_change')) {
            return true;
        }

        return $key === 'code' && in_array(self::$queryTable, ['password_reset_codes', 'password_reset_tokens'], true);
    }
}
