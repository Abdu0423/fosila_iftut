<?php

namespace App\Services;

use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    protected $login;
    protected $hash;
    protected $sender;
    protected $server;
    protected $loggingEnabled;

    public function __construct()
    {
        $config = config('sms.drivers.osonsms');
        $this->login = $config['login'];
        $this->hash = $config['hash'];
        $this->sender = $config['sender'];
        $this->server = $config['server'];
        $this->loggingEnabled = config('sms.logging.enabled', true);
    }

    /**
     * Отправить SMS сообщение
     *
     * @param string|array $phone Номер телефона или массив номеров
     * @param string $message Текст сообщения
     * @param int|null $userId ID пользователя (опционально)
     * @return array
     */
    public function send($phone, string $message, ?int $userId = null): array
    {
        try {
            // Нормализация номера телефона
            $phones = is_array($phone) ? $phone : [$phone];
            $normalizedPhones = array_map([$this, 'normalizePhone'], $phones);

            // Логирование отправки
            if ($this->loggingEnabled) {
                Log::channel(config('sms.logging.channel'))->info('SMS отправка', [
                    'phones' => $normalizedPhones,
                    'message' => $message,
                    'sender' => $this->sender,
                ]);
            }

            $responses = [];
            foreach ($normalizedPhones as $normalizedPhone) {
                $response = $this->sendSingle($normalizedPhone, $message, $userId);
                $responses[] = $response;
            }

            return [
                'success' => true,
                'responses' => $responses,
                'message' => 'SMS успешно отправлено',
            ];
        } catch (Exception $e) {
            if ($this->loggingEnabled) {
                Log::channel(config('sms.logging.channel'))->error('Ошибка отправки SMS', [
                    'phone' => $phone,
                    'message' => $message,
                    'error' => $e->getMessage(),
                ]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Не удалось отправить SMS',
            ];
        }
    }

    /**
     * Отправить SMS на один номер
     *
     * @param string $phone Номер телефона
     * @param string $message Текст сообщения
     * @param int|null $userId ID пользователя (опционально)
     * @return array
     */
    protected function sendSingle(string $phone, string $message, ?int $userId = null): array
    {
        // Создаем запись в базе данных
        $smsMessage = SmsMessage::create([
            'phone' => $phone,
            'message' => $message,
            'sender' => $this->sender,
            'status' => 'pending',
            'user_id' => $userId,
        ]);

        try {
            $response = Http::get($this->server, [
                'login' => $this->login,
                'hash' => $this->hash,
                'sender' => $this->sender,
                'phone' => $phone,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $body = $response->body();
                
                // Обновляем запись в базе данных
                $smsMessage->markAsSent($body);
                
                if ($this->loggingEnabled) {
                    Log::channel(config('sms.logging.channel'))->info('OsonSMS ответ', [
                        'phone' => $phone,
                        'response' => $body,
                    ]);
                }

                return [
                    'phone' => $phone,
                    'status' => 'sent',
                    'response' => $body,
                    'sms_message_id' => $smsMessage->id,
                ];
            }

            $error = 'OsonSMS API вернул ошибку: ' . $response->body();
            $smsMessage->markAsFailed($error);
            throw new Exception($error);
        } catch (Exception $e) {
            // Обновляем запись в базе данных при ошибке
            if ($smsMessage && $smsMessage->status === 'pending') {
                $smsMessage->markAsFailed($e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Нормализация номера телефона
     *
     * @param string $phone
     * @return string
     */
    protected function normalizePhone(string $phone): string
    {
        // Удаляем все символы кроме цифр и +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Если номер начинается с 992, оставляем как есть
        if (str_starts_with($phone, '992')) {
            return $phone;
        }

        // Если номер начинается с +992, убираем +
        if (str_starts_with($phone, '+992')) {
            return substr($phone, 1);
        }

        // Если номер начинается с 0, заменяем на 992
        if (str_starts_with($phone, '0')) {
            return '992' . substr($phone, 1);
        }

        // Если номер начинается с 9, добавляем 992
        if (str_starts_with($phone, '9') && strlen($phone) === 9) {
            return '992' . $phone;
        }

        return $phone;
    }

    /**
     * Отправить SMS используя шаблон
     *
     * @param string|array $phone
     * @param string $templateName
     * @param array $variables
     * @return array
     */
    public function sendTemplate($phone, string $templateName, array $variables = []): array
    {
        $template = config("sms.templates.{$templateName}");

        if (!$template) {
            throw new Exception("SMS шаблон '{$templateName}' не найден");
        }

        // Замена переменных в шаблоне
        $message = $template;
        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $this->send($phone, $message);
    }

    /**
     * Отправить код верификации
     *
     * @param string $phone
     * @param string $code
     * @return array
     */
    public function sendVerificationCode(string $phone, string $code): array
    {
        return $this->sendTemplate($phone, 'verification_code', [
            'code' => $code,
        ]);
    }

    /**
     * Отправить код сброса пароля
     *
     * @param string $phone
     * @param string $code
     * @return array
     */
    public function sendPasswordResetCode(string $phone, string $code): array
    {
        return $this->sendTemplate($phone, 'password_reset', [
            'code' => $code,
        ]);
    }

    /**
     * Отправить уведомление о входе
     *
     * @param string $phone
     * @return array
     */
    public function sendLoginNotification(string $phone): array
    {
        return $this->sendTemplate($phone, 'login_notification');
    }

    /**
     * Отправить уведомление о новом задании
     *
     * @param string $phone
     * @param string $title
     * @param string $deadline
     * @return array
     */
    public function sendAssignmentNotification(string $phone, string $title, string $deadline): array
    {
        return $this->sendTemplate($phone, 'assignment_notification', [
            'title' => $title,
            'deadline' => $deadline,
        ]);
    }

    /**
     * Отправить уведомление об оценке
     *
     * @param string $phone
     * @param string $grade
     * @param string $subject
     * @return array
     */
    public function sendGradeNotification(string $phone, string $grade, string $subject): array
    {
        return $this->sendTemplate($phone, 'grade_notification', [
            'grade' => $grade,
            'subject' => $subject,
        ]);
    }

    /**
     * Проверить баланс (если API поддерживает)
     *
     * @return array
     */
    public function checkBalance(): array
    {
        // OsonSMS может не иметь отдельного endpoint для проверки баланса
        // Реализуйте, если API предоставляет такую возможность
        return [
            'success' => false,
            'message' => 'Функция проверки баланса не реализована',
        ];
    }
}

