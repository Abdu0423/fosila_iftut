<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordResetCode;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Показать страницу "Забыли пароль"
     */
    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Отправить код подтверждения на телефон
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:9',
        ], [
            'phone.required' => 'Необходимо указать номер телефона',
            'phone.min' => 'Номер телефона должен содержать минимум 9 цифр',
        ]);

        // Нормализуем телефон
        $phone = $this->normalizePhone($request->phone);
        
        if (!$phone) {
            return back()->withErrors([
                'phone' => 'Неверный формат номера телефона'
            ]);
        }

        // Проверяем, существует ли пользователь с таким телефоном
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            return back()->withErrors([
                'phone' => 'Пользователь с таким номером телефона не найден'
            ]);
        }

        // Проверяем, можно ли отправить новый код (прошла ли 1 минута)
        if (!PasswordResetCode::canSendNewCode($phone)) {
            return back()->withErrors([
                'phone' => 'Повторная отправка SMS возможна через 1 минуту после предыдущей отправки'
            ]);
        }

        // Удаляем старые коды для этого телефона
        PasswordResetCode::where('phone', $phone)->delete();

        // Генерируем 6-значный код
        $code = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Создаем запись о коде
        $resetCode = PasswordResetCode::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
            'used' => false,
        ]);

        // Отправляем SMS с кодом
        try {
            $message = "Ваш код для восстановления пароля: {$code}. Действителен 5 минут.";
            $result = $this->smsService->send($phone, $message, $user->id);
            
            if (!$result['success']) {
                $resetCode->delete();
                return back()->withErrors([
                    'phone' => 'Не удалось отправить SMS. Попробуйте позже.'
                ]);
            }

            // Перенаправляем на страницу ввода кода
            return redirect()->route('password.reset', ['phone' => $phone])
                ->with('success', 'Код подтверждения отправлен на ваш номер телефона');
        } catch (\Exception $e) {
            $resetCode->delete();
            return back()->withErrors([
                'phone' => 'Ошибка при отправке SMS: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Показать форму сброса пароля
     */
    public function showResetForm(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $this->normalizePhone($request->phone);
        
        if (!$phone) {
            return redirect()->route('password.forgot')->withErrors([
                'phone' => 'Неверный формат номера телефона'
            ]);
        }

        // Проверяем, есть ли активный код для этого телефона
        $hasActiveCode = PasswordResetCode::where('phone', $phone)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->exists();

        if (!$hasActiveCode) {
            return redirect()->route('password.forgot')->withErrors([
                'phone' => 'Код подтверждения не найден или истек. Запросите новый код.'
            ]);
        }

        return Inertia::render('Auth/ResetPassword', [
            'phone' => $phone,
        ]);
    }

    /**
     * Сбросить пароль
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:4|confirmed',
        ], [
            'password.required' => 'Необходимо указать новый пароль',
            'password.min' => 'Пароль должен содержать минимум 4 символа',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        $phone = $this->normalizePhone($request->phone);
        
        if (!$phone) {
            return back()->withErrors([
                'phone' => 'Неверный формат номера телефона'
            ]);
        }

        // Проверяем код
        $resetCode = PasswordResetCode::where('phone', $phone)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetCode) {
            return back()->withErrors([
                'code' => 'Неверный или истекший код подтверждения'
            ]);
        }

        // Находим пользователя
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            return back()->withErrors([
                'phone' => 'Пользователь не найден'
            ]);
        }

        // Обновляем пароль
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        // Помечаем код как использованный
        $resetCode->markAsUsed();

        // Удаляем все коды для этого телефона
        PasswordResetCode::where('phone', $phone)->delete();

        return redirect()->route('login')->with('success', 'Пароль успешно изменен. Войдите с новым паролем.');
    }

    /**
     * Нормализация номера телефона
     */
    private function normalizePhone(string $phone): ?string
    {
        // Удаляем все символы кроме цифр и +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Если пусто после очистки или только +992, возвращаем null
        if (empty($phone) || $phone === '+992' || $phone === '992') {
            return null;
        }

        // Если номер уже начинается с +992, возвращаем как есть
        if (str_starts_with($phone, '+992')) {
            // Проверяем, что после +992 идет 9 цифр
            $digits = substr($phone, 4);
            if (preg_match('/^\d{9}$/', $digits)) {
                return $phone;
            }
            return null;
        }

        // Если номер начинается с 992 (без +), добавляем +
        if (str_starts_with($phone, '992')) {
            $digits = substr($phone, 3);
            if (preg_match('/^\d{9}$/', $digits)) {
                return '+' . $phone;
            }
            return null;
        }

        // Если номер начинается с 0, заменяем на +992
        if (str_starts_with($phone, '0')) {
            $digits = substr($phone, 1);
            if (preg_match('/^\d{9}$/', $digits)) {
                return '+992' . $digits;
            }
            return null;
        }

        // Если номер начинается с 9 и имеет 9 цифр, добавляем +992
        if (str_starts_with($phone, '9') && preg_match('/^\d{9}$/', $phone)) {
            return '+992' . $phone;
        }

        // Если номер состоит только из цифр (9 цифр), добавляем +992
        if (preg_match('/^\d{9}$/', $phone)) {
            return '+992' . $phone;
        }

        // Если ничего не подошло, возвращаем null
        return null;
    }
}
