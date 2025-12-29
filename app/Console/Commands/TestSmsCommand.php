<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsService;

class TestSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправить тестовое SMS сообщение';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone') ?? '+992989190423';
        $message = 'Тестовое сообщение от системы IFTUT.TJ. Время: ' . now()->format('Y-m-d H:i:s');
        
        $this->info("Отправка тестового SMS на номер: {$phone}");
        $this->info("Сообщение: {$message}");
        
        try {
            $smsService = new SmsService();
            $result = $smsService->send($phone, $message);
            
            if ($result['success']) {
                $this->info("✓ SMS успешно отправлено!");
                $this->line("Ответ: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->error("✗ Ошибка отправки SMS");
                $this->line("Ошибка: " . ($result['error'] ?? 'Неизвестная ошибка'));
            }
        } catch (\Exception $e) {
            $this->error("✗ Исключение: " . $e->getMessage());
            $this->line("Трассировка: " . $e->getTraceAsString());
        }
    }
}
