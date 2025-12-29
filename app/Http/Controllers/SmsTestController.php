<?php

namespace App\Http\Controllers;

use App\Facades\Sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SmsTestController extends Controller
{
    /**
     * Показать форму тестирования SMS
     */
    public function index()
    {
        return view('sms-test');
    }

    /**
     * Отправить тестовое SMS
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string|max:160',
        ], [
            'phone.required' => 'Введите номер телефона',
            'message.required' => 'Введите текст сообщения',
            'message.max' => 'Сообщение не должно превышать 160 символов',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = Sms::send($request->phone, $request->message);

        return response()->json($result);
    }

    /**
     * Отправить код верификации
     */
    public function sendVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $code = rand(100000, 999999);
        $result = Sms::sendVerificationCode($request->phone, $code);

        return response()->json(array_merge($result, ['code' => $code]));
    }

    /**
     * Проверить конфигурацию
     */
    public function checkConfig()
    {
        $config = config('sms.drivers.osonsms');
        
        return response()->json([
            'configured' => !empty($config['login']) && !empty($config['pass_salt_hash']),
            'login' => $config['login'] ?? 'не установлено',
            'sender_name' => $config['sender_name'] ?? 'не установлено',
            'server_url' => $config['server_url'] ?? 'не установлено',
            'pass_salt_hash_set' => !empty($config['pass_salt_hash']),
            'logging_enabled' => config('sms.logging.enabled'),
        ]);
    }
}

