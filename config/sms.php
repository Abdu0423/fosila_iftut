<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Service Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your SMS service settings. The application uses
    | OsonSMS as the default SMS provider for sending SMS notifications.
    |
    */

    'default' => env('SMS_DRIVER', 'osonsms'),

    'drivers' => [
        'osonsms' => [
            'login' => env('SMS_LOGIN', 'iftuttj'),
            'pass_salt_hash' => env('SMS_HASH', '39dc0b8ddfe0afb8ca4637fb3d895e18'),
            'sender_name' => env('SMS_SENDER', 'IFTUT.TJ'),
            'server_url' => env('SMS_SERVER', 'https://api.osonsms.com/sendsms_v1.php'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Templates
    |--------------------------------------------------------------------------
    |
    | Here you may define SMS message templates for various notifications.
    |
    */

    'templates' => [
        'verification_code' => 'Ваш код подтверждения: {code}. IFTUT.TJ',
        'password_reset' => 'Код для сброса пароля: {code}. Не сообщайте никому. IFTUT.TJ',
        'login_notification' => 'Вход в систему IFTUT.TJ. Если это не вы, срочно смените пароль.',
        'assignment_notification' => 'Новое задание: {title}. Срок: {deadline}. IFTUT.TJ',
        'grade_notification' => 'Получена новая оценка {grade} по предмету {subject}. IFTUT.TJ',
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable SMS logging for debugging purposes.
    |
    */

    'logging' => [
        'enabled' => env('SMS_LOGGING_ENABLED', true),
        'channel' => env('SMS_LOGGING_CHANNEL', 'daily'),
    ],

];

