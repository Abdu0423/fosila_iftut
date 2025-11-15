# 📱 Интеграция OsonSMS - Готово к использованию

## ✅ Что было сделано

1. **Создан конфигурационный файл** `config/sms.php`
2. **Создан SMS сервис** `app/Services/SmsService.php`
3. **Создан фасад** `app/Facades/Sms.php`
4. **Зарегистрирован Service Provider** в `config/app.php`
5. **Созданы контроллеры**:
   - `SmsTestController` - для тестирования
   - `SmsNotificationController` - для уведомлений
6. **Добавлены тестовые маршруты** в `routes/web.php`
7. **Создана документация** `docs/SMS_INTEGRATION.md`

## 🚀 Быстрый старт

### Шаг 1: Добавьте в `.env` файл:

```env
SMS_LOGIN=iftuttj
SMS_HASH=39dc0b8ddfe0afb8ca4637fb3d895e18
SMS_SENDER=IFTUT.TJ
SMS_SERVER=https://api.osonsms.com/sendsms_v1.php
```

### Шаг 2: Очистите кэш (уже выполнено)

```bash
php artisan config:clear
php artisan optimize:clear
```

### Шаг 3: Проверьте конфигурацию

Откройте в браузере (после авторизации):
```
http://127.0.0.1:8000/sms-test/check-config
```

## 💡 Примеры использования

### Простая отправка SMS

```php
use App\Facades\Sms;

Sms::send('992918123456', 'Привет от IFTUT!');
```

### Отправка кода верификации

```php
Sms::sendVerificationCode('992918123456', '123456');
```

### Уведомление об оценке

```php
Sms::sendGradeNotification('992918123456', '5', 'Математика');
```

### Уведомление о задании

```php
Sms::sendAssignmentNotification(
    '992918123456',
    'Решение уравнений',
    '25.12.2025'
);
```

### В контроллере

```php
use App\Facades\Sms;

class GradeController extends Controller
{
    public function store(Request $request)
    {
        $grade = Grade::create($request->validated());
        
        // Отправляем SMS уведомление
        if ($grade->student->phone) {
            Sms::sendGradeNotification(
                $grade->student->phone,
                $grade->grade,
                $grade->subject->name
            );
        }
        
        return response()->json($grade);
    }
}
```

## 🧪 Тестирование

### Через API:

```bash
# Проверка конфигурации
curl http://127.0.0.1:8000/sms-test/check-config

# Отправка тестового SMS (требуется авторизация)
curl -X POST http://127.0.0.1:8000/sms-test/send \
  -H "Content-Type: application/json" \
  -d '{"phone":"992918123456","message":"Test"}'
```

### Через Tinker:

```bash
php artisan tinker
>>> \App\Facades\Sms::send('992918123456', 'Тест');
>>> \App\Facades\Sms::sendVerificationCode('992918123456', '123456');
```

## 📋 Доступные методы

| Метод | Описание |
|-------|----------|
| `Sms::send($phone, $message)` | Простая отправка SMS |
| `Sms::sendVerificationCode($phone, $code)` | Код верификации |
| `Sms::sendPasswordResetCode($phone, $code)` | Код сброса пароля |
| `Sms::sendLoginNotification($phone)` | Уведомление о входе |
| `Sms::sendAssignmentNotification($phone, $title, $deadline)` | Уведомление о задании |
| `Sms::sendGradeNotification($phone, $grade, $subject)` | Уведомление об оценке |
| `Sms::sendTemplate($phone, $template, $vars)` | Отправка по шаблону |

## 🎯 Готовые сценарии использования

### 1. Уведомление при создании оценки

```php
// В Admin\GradeController или Teacher\GradeController
use App\Facades\Sms;

public function store(Request $request)
{
    $grade = Grade::create($request->validated());
    
    if ($grade->student->phone) {
        Sms::sendGradeNotification(
            $grade->student->phone,
            $grade->grade,
            optional($grade->schedule->subject)->name ?? 'предмету'
        );
    }
    
    return redirect()->back()->with('success', 'Оценка добавлена и SMS отправлено');
}
```

### 2. Уведомление при создании задания

```php
// В Admin\AssignmentController
use App\Facades\Sms;

public function store(Request $request)
{
    $assignment = Assignment::create($request->validated());
    
    // Отправляем SMS всем студентам группы
    if ($assignment->group_id) {
        $students = User::where('group_id', $assignment->group_id)
            ->whereNotNull('phone')
            ->get();
            
        foreach ($students as $student) {
            Sms::sendAssignmentNotification(
                $student->phone,
                $assignment->title,
                $assignment->due_date->format('d.m.Y')
            );
        }
    }
    
    return redirect()->back()->with('success', 'Задание создано, SMS отправлены');
}
```

### 3. Двухфакторная аутентификация

```php
// В AuthController
use App\Facades\Sms;

public function sendLoginCode(Request $request)
{
    $user = User::where('email', $request->email)->first();
    
    if ($user && $user->phone) {
        $code = rand(100000, 999999);
        cache()->put("login_code_{$user->id}", $code, now()->addMinutes(5));
        
        Sms::sendVerificationCode($user->phone, $code);
        
        return response()->json([
            'message' => 'Код отправлен на ваш телефон',
            'phone_last_4' => substr($user->phone, -4)
        ]);
    }
    
    return response()->json(['message' => 'Пользователь не найден'], 404);
}
```

## 📊 Логирование

Все SMS логируются в `storage/logs/laravel.log`:

```
[2025-11-15 10:30:00] local.INFO: SMS отправка {"phones":["992918123456"],"message":"Тест","sender":"IFTUT.TJ"}
[2025-11-15 10:30:01] local.INFO: OsonSMS ответ {"phone":"992918123456","response":"OK"}
```

## ⚠️ Важно для production

1. **Удалите тестовые маршруты** `/sms-test/*` из `routes/web.php`
2. **Добавьте rate limiting** для предотвращения спама
3. **Не коммитьте `.env`** в Git
4. **Проверяйте баланс** на osonsms.com
5. **Добавьте мониторинг расходов**

## 🔒 Безопасность

```php
// Добавьте rate limiting в RouteServiceProvider
RateLimiter::for('sms', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
});

// Защитите маршруты
Route::post('/send-sms', function() {
    // ...
})->middleware('throttle:sms');
```

## 📚 Полная документация

См. файл `docs/SMS_INTEGRATION.md` для подробной документации.

## 🐛 Устранение неполадок

### SMS не отправляется

1. Проверьте `.env` файл - все переменные установлены?
2. Очистите кэш: `php artisan config:clear`
3. Проверьте логи: `tail -f storage/logs/laravel.log`
4. Проверьте баланс на osonsms.com
5. Убедитесь что номер в правильном формате (992...)

### Ошибка "Class 'Sms' not found"

```bash
php artisan config:clear
php artisan optimize:clear
composer dump-autoload
```

## 🆘 Поддержка

- Техподдержка OsonSMS: https://osonsms.com
- Документация API: https://api.osonsms.com/docs

---

**Статус: ✅ Готово к использованию**

Просто добавьте переменные в `.env` и начинайте отправлять SMS! 🚀

