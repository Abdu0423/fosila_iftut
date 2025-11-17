# Система обязательной смены пароля при первом входе

## Описание

Система автоматически требует от новых пользователей сменить пароль при первом входе в систему. Это повышает безопасность, так как временные пароли, созданные администратором, должны быть заменены на известные только пользователю.

## Компоненты системы

### 1. База данных

**Миграция**: `database/migrations/2025_11_15_170214_add_must_change_password_to_users_table.php`

Добавляет поле `must_change_password` (boolean) в таблицу `users`:

```php
$table->boolean('must_change_password')->default(false);
```

### 2. Модель User

**Файл**: `app/Models/User.php`

Обновлено:
- Добавлено `must_change_password` в `$fillable`
- Добавлено `'must_change_password' => 'boolean'` в `$casts`

### 3. Middleware

**Файл**: `app/Http/Middleware/CheckPasswordChange.php`

Проверяет, требуется ли пользователю сменить пароль:

- Если `must_change_password = true`, перенаправляет на `/change-password`
- Пропускает страницы смены пароля и выхода из системы
- Применяется ко всем защищенным маршрутам (admin, teacher, student)

**Регистрация**: `app/Http/Kernel.php`

```php
'check.password.change' => \App\Http\Middleware\CheckPasswordChange::class,
```

### 4. Контроллер

**Файл**: `app/Http/Controllers/ChangePasswordController.php`

**Методы**:
- `show()` - Отображает страницу смены пароля
- `update()` - Обрабатывает смену пароля и перенаправляет в соответствующую панель

**Валидация**:
- Текущий пароль (обязательный)
- Новый пароль (минимум 8 символов, с подтверждением)
- Новый пароль должен отличаться от текущего

### 5. Vue компонент

**Файл**: `resources/js/Pages/Auth/ChangePassword.vue`

**Возможности**:
- Красивая форма с градиентным фоном
- Отображение требований к паролю в реальном времени
- Индикатор силы пароля
- Показ/скрытие пароля
- Информация о текущем пользователе
- Кнопка выхода из системы

### 6. Маршруты

**Файл**: `routes/web.php`

```php
// Маршруты смены пароля (доступны авторизованным пользователям)
Route::middleware('auth')->group(function () {
    Route::get('/change-password', [ChangePasswordController::class, 'show'])
        ->name('change-password');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])
        ->name('change-password.update');
});

// Middleware добавлен ко всем защищенным группам
Route::prefix('admin')->middleware(['auth', 'admin', 'check.password.change'])->group(...);
Route::prefix('teacher')->middleware(['auth', 'teacher', 'check.password.change'])->group(...);
Route::prefix('student')->middleware(['auth', 'student', 'check.password.change'])->group(...);
```

## Как это работает

### Создание пользователя

Когда администратор создает нового пользователя через `Admin\UserController`:

```php
$user = User::create([
    // ... другие поля
    'password' => bcrypt($validated['password']),
    'must_change_password' => true // Флаг устанавливается автоматически
]);
```

### Первый вход пользователя

1. Пользователь входит с временным паролем
2. Middleware `CheckPasswordChange` проверяет флаг `must_change_password`
3. Если флаг = `true`, пользователь перенаправляется на `/change-password`
4. Все другие страницы недоступны до смены пароля

### Смена пароля

1. Пользователь вводит текущий (временный) пароль
2. Вводит новый пароль дважды
3. Система проверяет:
   - Правильность текущего пароля
   - Соответствие требованиям (минимум 8 символов)
   - Совпадение подтверждения
   - Что новый пароль отличается от старого
4. После успешной смены:
   - Устанавливается `must_change_password = false`
   - Пользователь перенаправляется на свою панель

### Перенаправление после смены

В зависимости от роли пользователя:
- **Admin** → `/admin`
- **Teacher** → `/teacher`
- **Student** → `/student`

## Требования к паролю

### Обязательные
- Минимум 8 символов

### Рекомендуемые (отображаются в форме)
- Хотя бы одна заглавная буква (A-Z)
- Хотя бы одна строчная буква (a-z)
- Хотя бы одна цифра (0-9)
- Специальные символы (опционально)

### Оценка силы пароля

| Уровень | Критерии | Цвет |
|---------|----------|------|
| Слабый | 2 или меньше критериев | Красный |
| Средний | 3-4 критерия | Оранжевый |
| Надежный | 5-6 критериев | Зеленый |

## Безопасность

### Реализованные меры

1. **Хеширование паролей**: Используется bcrypt
2. **Валидация**: Проверка текущего пароля перед сменой
3. **Уникальность**: Новый пароль должен отличаться от текущего
4. **Защита от обхода**: Middleware блокирует все страницы кроме смены пароля
5. **CSRF защита**: Laravel CSRF token на всех формах

### Рекомендации

1. Установите более строгие требования к паролям в production:

```php
Password::min(8)
    ->mixedCase()
    ->numbers()
    ->symbols()
    ->uncompromised()
```

2. Добавьте ограничение попыток смены пароля (rate limiting)
3. Включите логирование смены паролей
4. Отправляйте email уведомления о смене пароля

## Управление

### Установить флаг вручную

```php
$user = User::find($userId);
$user->update(['must_change_password' => true]);
```

### Сбросить флаг вручную

```php
$user = User::find($userId);
$user->update(['must_change_password' => false]);
```

### Массовая установка флага

Для всех существующих пользователей:

```php
User::where('must_change_password', false)->update(['must_change_password' => true]);
```

Для конкретной роли:

```php
User::whereHas('role', function ($query) {
    $query->where('name', 'student');
})->update(['must_change_password' => true]);
```

## Интеграция с SMS уведомлениями

Можно добавить отправку SMS с кодом подтверждения:

```php
use App\Facades\Sms;

// В ChangePasswordController::update()
if ($user->phone) {
    $code = rand(100000, 999999);
    cache()->put("password_change_code_{$user->id}", $code, now()->addMinutes(5));
    Sms::sendVerificationCode($user->phone, $code);
}
```

## Тестирование

### Создание тестового пользователя

```bash
php artisan tinker
```

```php
$user = User::create([
    'name' => 'Тест',
    'last_name' => 'Пользователь',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'role_id' => 3, // student
    'must_change_password' => true
]);
```

### Проверка работы

1. Войдите как тестовый пользователь
2. Должно автоматически перенаправить на `/change-password`
3. Попробуйте перейти на другие страницы (должно вернуть на смену пароля)
4. Смените пароль
5. Проверьте что флаг сброшен: `User::find($userId)->must_change_password` должен быть `false`
6. Проверьте доступ к панели

## Устранение неполадок

### Пользователь не перенаправляется на страницу смены пароля

- Проверьте что middleware зарегистрирован в `Kernel.php`
- Убедитесь что middleware добавлен к маршрутам
- Очистите кэш: `php artisan optimize:clear`

### Ошибка "Route [change-password] not found"

- Проверьте что маршруты зарегистрированы в `routes/web.php`
- Очистите кэш маршрутов: `php artisan route:clear`
- Проверьте список маршрутов: `php artisan route:list | findstr change`

### После смены пароля пользователь снова попадает на страницу смены

- Проверьте что `must_change_password` сбрасывается в `false`
- Проверьте логи: `storage/logs/laravel.log`
- Проверьте в БД: `SELECT must_change_password FROM users WHERE id = ?`

### Страница смены пароля не отображается

- Пересоберите фронтенд: `npm run build`
- Очистите кэш браузера
- Проверьте консоль браузера на ошибки

## Дополнительные возможности

### Срок действия пароля

Добавьте поле `password_expires_at`:

```php
Schema::table('users', function (Blueprint $table) {
    $table->timestamp('password_expires_at')->nullable();
});
```

Обновите middleware:

```php
if ($user->password_expires_at && $user->password_expires_at->isPast()) {
    return redirect()->route('change-password')
        ->with('warning', 'Срок действия вашего пароля истек');
}
```

### История паролей

Предотвратить повторное использование старых паролей:

```php
Schema::create('password_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('password');
    $table->timestamp('created_at');
});
```

## Поддержка

При возникновении проблем:
1. Проверьте логи: `storage/logs/laravel.log`
2. Убедитесь что миграция выполнена: `php artisan migrate:status`
3. Очистите все кэши: `php artisan optimize:clear`
4. Пересоберите фронтенд: `npm run build`

---

**Статус**: ✅ Полностью реализовано и готово к использованию

