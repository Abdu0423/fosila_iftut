<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array send(string|array $phone, string $message)
 * @method static array sendTemplate(string|array $phone, string $templateName, array $variables = [])
 * @method static array sendVerificationCode(string $phone, string $code)
 * @method static array sendPasswordResetCode(string $phone, string $code)
 * @method static array sendLoginNotification(string $phone)
 * @method static array sendAssignmentNotification(string $phone, string $title, string $deadline)
 * @method static array sendGradeNotification(string $phone, string $grade, string $subject)
 * @method static array checkBalance()
 *
 * @see \App\Services\SmsService
 */
class Sms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sms.service';
    }
}

