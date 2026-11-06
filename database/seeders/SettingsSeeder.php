<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'Fosila',
                'type' => 'string',
                'description' => 'Название сайта'
            ],
            [
                'key' => 'site_description',
                'value' => 'Система дистанционного обучения',
                'type' => 'string',
                'description' => 'Описание сайта'
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@fosila.com',
                'type' => 'string',
                'description' => 'Email администратора'
            ],
            [
                'key' => 'max_file_size',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Максимальный размер файла в МБ'
            ],
            [
                'key' => 'allowed_file_types',
                'value' => 'pdf,doc,docx,jpg,jpeg,png',
                'type' => 'string',
                'description' => 'Разрешенные типы файлов'
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Режим обслуживания'
            ],
            [
                'key' => 'registration_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Разрешить регистрацию пользователей'
            ],
            [
                'key' => 'email_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Email уведомления'
            ],
            [
                'key' => 'default_timezone',
                'value' => 'Asia/Dushanbe',
                'type' => 'string',
                'description' => 'Часовой пояс по умолчанию'
            ],
            [
                'key' => 'session_timeout',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Таймаут сессии в минутах'
            ],
            [
                'key' => 'backup_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Автоматическое резервное копирование'
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'type' => 'string',
                'description' => 'Частота резервного копирования'
            ],
            [
                'key' => 'smtp_host',
                'value' => '',
                'type' => 'string',
                'description' => 'SMTP хост'
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'type' => 'integer',
                'description' => 'SMTP порт'
            ],
            [
                'key' => 'smtp_username',
                'value' => '',
                'type' => 'string',
                'description' => 'SMTP пользователь'
            ],
            [
                'key' => 'smtp_password',
                'value' => '',
                'type' => 'string',
                'description' => 'SMTP пароль'
            ],
            [
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'type' => 'string',
                'description' => 'SMTP шифрование'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
