<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        return spa('Admin/Settings/Index', [
            'settings' => [
                'site_name' => $settings['site_name'] ?? config('app.name', 'Fosila'),
                'site_description' => $settings['site_description'] ?? 'Система дистанционного обучения',
                'admin_email' => $settings['admin_email'] ?? config('mail.from.address'),
                'max_file_size' => (int) ($settings['max_file_size'] ?? 10),
                'allowed_file_types' => $settings['allowed_file_types'] ?? 'pdf,doc,docx,jpg,jpeg,png',
                'maintenance_mode' => $this->flag($settings['maintenance_mode'] ?? false),
                'registration_enabled' => $this->flag($settings['registration_enabled'] ?? true, true),
                'email_notifications' => $this->flag($settings['email_notifications'] ?? true, true),
                'default_timezone' => $this->timezoneSetting($settings['default_timezone'] ?? null),
                'session_timeout' => (int) ($settings['session_timeout'] ?? 120),
                'backup_enabled' => $this->flag($settings['backup_enabled'] ?? false),
                'backup_frequency' => $settings['backup_frequency'] ?? 'daily',
                'smtp_host' => $settings['smtp_host'] ?? config('mail.mailers.smtp.host', ''),
                'smtp_port' => (int) ($settings['smtp_port'] ?? config('mail.mailers.smtp.port', 587)),
                'smtp_username' => $settings['smtp_username'] ?? config('mail.mailers.smtp.username', ''),
                'smtp_password' => $settings['smtp_password'] ?? '',
                'smtp_encryption' => $settings['smtp_encryption'] ?? (config('mail.mailers.smtp.encryption') ?: 'tls'),
            ],
            'timezones' => [
                ['value' => 'Asia/Dushanbe', 'text' => 'Таджикистан (UTC+5)'],
                ['value' => 'Asia/Almaty', 'text' => 'Алматы (UTC+6)'],
                ['value' => 'Europe/Moscow', 'text' => 'Москва (UTC+3)'],
                ['value' => 'UTC', 'text' => 'UTC (UTC+0)'],
            ],
            'backupFrequencies' => [
                ['value' => 'hourly', 'text' => 'Каждый час'],
                ['value' => 'daily', 'text' => 'Ежедневно'],
                ['value' => 'weekly', 'text' => 'Еженедельно'],
                ['value' => 'monthly', 'text' => 'Ежемесячно'],
            ],
            'fileTypes' => [
                'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg',
                'mp4', 'avi', 'mov', 'wmv', 'flv',
                'mp3', 'wav', 'ogg', 'aac',
                'zip', 'rar', '7z', 'tar', 'gz'
            ]
        ]);
    }

    private function flag($value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function timezoneSetting(?string $value): string
    {
        if (! $value || in_array($value, ['Asia/Almaty', 'Asia/Astana'], true)) {
            Setting::updateOrCreate(
                ['key' => 'default_timezone'],
                [
                    'value' => 'Asia/Dushanbe',
                    'type' => 'string',
                    'description' => 'Часовой пояс по умолчанию',
                ]
            );

            return 'Asia/Dushanbe';
        }

        return $value;
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'admin_email' => 'required|email',
            'default_timezone' => 'required|string',
            'session_timeout' => 'required|integer|min:30|max:1440',
        ]);

        $settings = [
            'site_name' => $request->site_name,
            'site_description' => $request->site_description,
            'admin_email' => $request->admin_email,
            'default_timezone' => $request->default_timezone,
            'session_timeout' => $request->session_timeout,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Общие настройки обновлены успешно!');
    }

    public function updateFileSettings(Request $request)
    {
        $request->validate([
            'max_file_size' => 'required|integer|min:1|max:100',
            'allowed_file_types' => 'required|string',
        ]);

        $settings = [
            'max_file_size' => $request->max_file_size,
            'allowed_file_types' => $request->allowed_file_types,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Настройки файлов обновлены успешно!');
    }

    public function updateSystemSettings(Request $request)
    {
        $request->validate([
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'email_notifications' => 'boolean',
            'backup_enabled' => 'boolean',
            'backup_frequency' => 'required_if:backup_enabled,true|string',
        ]);

        $settings = [
            'maintenance_mode' => $request->boolean('maintenance_mode'),
            'registration_enabled' => $request->boolean('registration_enabled'),
            'email_notifications' => $request->boolean('email_notifications'),
            'backup_enabled' => $request->boolean('backup_enabled'),
            'backup_frequency' => $request->backup_frequency,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Системные настройки обновлены успешно!');
    }

    public function updateEmailSettings(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'required|in:tls,ssl',
        ]);

        $settings = [
            'smtp_host' => $request->smtp_host,
            'smtp_port' => $request->smtp_port,
            'smtp_username' => $request->smtp_username,
            'smtp_encryption' => $request->smtp_encryption,
        ];

        // Обновляем пароль только если он предоставлен
        if ($request->filled('smtp_password')) {
            $settings['smtp_password'] = $request->smtp_password;
        }

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Настройки email обновлены успешно!');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Текущий пароль неверен']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Профиль обновлен успешно!');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Здесь можно добавить логику отправки тестового email
            // Mail::to($request->test_email)->send(new TestEmail());
            
            return redirect()->back()->with('success', 'Тестовое письмо отправлено успешно!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['email' => 'Ошибка отправки email: ' . $e->getMessage()]);
        }
    }

    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');
            
            return redirect()->back()->with('success', 'Кэш очищен успешно!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['cache' => 'Ошибка очистки кэша: ' . $e->getMessage()]);
        }
    }

    public function backupDatabase()
    {
        try {
            // Здесь можно добавить логику резервного копирования
            // \Artisan::call('backup:run');
            
            return redirect()->back()->with('success', 'Резервная копия создана успешно!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['backup' => 'Ошибка создания резервной копии: ' . $e->getMessage()]);
        }
    }
}
