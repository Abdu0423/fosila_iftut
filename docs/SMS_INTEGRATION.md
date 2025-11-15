# Интеграция OsonSMS

## Настройка

### 1. Добавьте переменные в `.env` файл:

```env
SMS_LOGIN=iftuttj
SMS_HASH=39dc0b8ddfe0afb8ca4637fb3d895e18
SMS_SENDER=IFTUT.TJ
SMS_SERVER=https://api.osonsms.com/sendsms_v1.php
SMS_LOGGING_ENABLED=true
SMS_LOGGING_CHANNEL=daily
```

### 2. Очистите кэш конфигурации:

```bash
php artisan config:clear
php artisan config:cache
```

## Использование

### Простая отправка SMS

```php
use App\Facades\Sms;

// Отправка одного SMS
Sms::send('992918123456', 'Привет! Это тестовое сообщение.');

// Отправка нескольким получателям
Sms::send(['992918123456', '992917654321'], 'Массовая рассылка');
```

### Использование шаблонов

```php
use App\Facades\Sms;

// Отправка кода верификации
Sms::sendVerificationCode('992918123456', '123456');

// Отправка кода сброса пароля
Sms::sendPasswordResetCode('992918123456', '789012');

// Уведомление о входе в систему
Sms::sendLoginNotification('992918123456');

// Уведомление о новом задании
Sms::sendAssignmentNotification(
    '992918123456',
    'Математика: Решение уравнений',
    '20.12.2025'
);

// Уведомление об оценке
Sms::sendGradeNotification(
    '992918123456',
    '5',
    'Математика'
);
```

### Использование с пользовательским шаблоном

```php
use App\Facades\Sms;

// Отправка с использованием кастомного шаблона
Sms::sendTemplate('992918123456', 'verification_code', [
    'code' => '123456'
]);
```

### В контроллере

```php
namespace App\Http\Controllers;

use App\Facades\Sms;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function sendVerificationCode(Request $request)
    {
        $phone = $request->input('phone');
        $code = rand(100000, 999999);
        
        // Сохраняем код в сессии или БД
        session(['verification_code' => $code]);
        
        // Отправляем SMS
        $result = Sms::sendVerificationCode($phone, $code);
        
        if ($result['success']) {
            return response()->json([
                'message' => 'Код отправлен на ваш номер телефона'
            ]);
        }
        
        return response()->json([
            'message' => 'Не удалось отправить SMS'
        ], 500);
    }
}
```

### С использованием Service напрямую

```php
use App\Services\SmsService;

class NotificationService
{
    protected $smsService;
    
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    
    public function notifyStudent($student, $message)
    {
        if ($student->phone) {
            return $this->smsService->send($student->phone, $message);
        }
    }
}
```

## Доступные шаблоны

В файле `config/sms.php` определены следующие шаблоны:

- `verification_code` - Код подтверждения
- `password_reset` - Код сброса пароля
- `login_notification` - Уведомление о входе
- `assignment_notification` - Уведомление о задании
- `grade_notification` - Уведомление об оценке

### Добавление своего шаблона

Отредактируйте `config/sms.php`:

```php
'templates' => [
    'custom_template' => 'Ваш текст с {variable}. IFTUT.TJ',
],
```

Использование:

```php
Sms::sendTemplate('992918123456', 'custom_template', [
    'variable' => 'значение'
]);
```

## Формат номера телефона

Сервис автоматически нормализует номера телефонов:

- `992918123456` ✓
- `+992918123456` → `992918123456` ✓
- `0918123456` → `992918123456` ✓
- `918123456` → `992918123456` ✓

## Логирование

Все SMS логируются в файлы логов Laravel (по умолчанию в `storage/logs/laravel.log`).

Для отключения логирования установите в `.env`:

```env
SMS_LOGGING_ENABLED=false
```

## Обработка ошибок

```php
$result = Sms::send('992918123456', 'Тест');

if ($result['success']) {
    // SMS успешно отправлено
    echo "Отправлено: " . $result['message'];
} else {
    // Ошибка отправки
    echo "Ошибка: " . $result['error'];
}
```

## Примеры интеграции

### Отправка SMS при регистрации

```php
use App\Facades\Sms;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $user = User::create($request->validated());
        
        // Отправляем приветственное SMS
        Sms::send(
            $user->phone,
            "Добро пожаловать в IFTUT.TJ! Ваш логин: {$user->email}"
        );
        
        return response()->json($user);
    }
}
```

### Уведомление о новой оценке

```php
use App\Facades\Sms;

class GradeController extends Controller
{
    public function store(Request $request)
    {
        $grade = Grade::create($request->validated());
        $student = $grade->student;
        
        // Отправляем уведомление студенту
        Sms::sendGradeNotification(
            $student->phone,
            $grade->grade,
            $grade->subject->name
        );
        
        return response()->json($grade);
    }
}
```

### Массовая рассылка группе

```php
use App\Facades\Sms;
use App\Models\Group;

class AnnouncementController extends Controller
{
    public function sendToGroup(Request $request, Group $group)
    {
        $message = $request->input('message');
        $phones = $group->students()->pluck('phone')->toArray();
        
        $result = Sms::send($phones, $message);
        
        return response()->json([
            'message' => 'Рассылка завершена',
            'recipients' => count($phones),
            'result' => $result
        ]);
    }
}
```

## Тестирование

Создайте тестовый маршрут для проверки:

```php
// routes/web.php
Route::get('/test-sms', function () {
    $result = \App\Facades\Sms::send('992918123456', 'Тестовое SMS от IFTUT.TJ');
    return response()->json($result);
});
```

Откройте в браузере: `http://your-domain.com/test-sms`

## Безопасность

⚠️ **Важно:**
- Не коммитьте `.env` файл в Git
- Храните `SMS_HASH` в секрете
- Используйте HTTPS для production
- Добавьте rate limiting для SMS endpoints
- Проверяйте номера телефонов перед отправкой

## Лимиты и расходы

- Уточните лимиты и тарифы у OsonSMS
- Рекомендуется настроить мониторинг расходов
- Добавьте throttling для предотвращения спама

## Поддержка

При возникновении проблем:
1. Проверьте логи: `storage/logs/laravel.log`
2. Убедитесь, что все переменные окружения установлены
3. Проверьте баланс в личном кабинете OsonSMS
4. Свяжитесь с технической поддержкой OsonSMS

