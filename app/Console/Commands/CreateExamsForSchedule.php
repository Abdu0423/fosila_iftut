<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use App\Models\Test;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class CreateExamsForSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:create {schedule_id : ID расписания}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать два периодических экзамена и один итоговый экзамен для расписания';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scheduleId = $this->argument('schedule_id');
        
        $schedule = Schedule::with(['subject', 'group', 'teacher'])->find($scheduleId);
        
        if (!$schedule) {
            $this->error('Расписание с ID ' . $scheduleId . ' не найдено');
            return 1;
        }

        $this->info('Расписание: ' . ($schedule->subject ? $schedule->subject->name : 'Не указан предмет'));
        $this->info('Группа: ' . ($schedule->group ? $schedule->group->name : 'Не указана группа'));
        $this->info('Учитель: ' . ($schedule->teacher ? $schedule->teacher->name : 'Не указан учитель'));

        // Проверяем, есть ли уже экзамены для этого расписания
        $existingExams = Test::where('schedule_id', $scheduleId)
            ->whereIn('exam_type', ['periodic', 'final'])
            ->get();

        if ($existingExams->isNotEmpty()) {
            $this->warn('Внимание: для этого расписания уже существуют экзамены:');
            foreach ($existingExams as $exam) {
                $this->line('  - ' . $exam->title . ' (' . $exam->exam_type . ')');
            }
            
            if (!$this->confirm('Продолжить и создать новые экзамены?')) {
                $this->info('Отменено');
                return 0;
            }
        }

        $teacher = $schedule->teacher;
        
        if (!$teacher) {
            $this->error('У расписания не указан учитель');
            return 1;
        }

        // Создаем два периодических экзамена
        $periodicExam1 = Test::create([
            'title' => 'Периодический экзамен 1 - ' . ($schedule->subject ? $schedule->subject->name : 'Предмет'),
            'description' => 'Первый периодический экзамен для ' . ($schedule->group ? $schedule->group->name : 'группы'),
            'schedule_id' => $schedule->id,
            'created_by' => $teacher->id,
            'time_limit' => 90, // 90 минут
            'passing_score' => 60, // 60%
            'max_attempts' => 3, // 3 попытки для периодических экзаменов
            'is_active' => true,
            'exam_type' => 'periodic',
        ]);

        $periodicExam2 = Test::create([
            'title' => 'Периодический экзамен 2 - ' . ($schedule->subject ? $schedule->subject->name : 'Предмет'),
            'description' => 'Второй периодический экзамен для ' . ($schedule->group ? $schedule->group->name : 'группы'),
            'schedule_id' => $schedule->id,
            'created_by' => $teacher->id,
            'time_limit' => 90, // 90 минут
            'passing_score' => 60, // 60%
            'max_attempts' => 3, // 3 попытки для периодических экзаменов
            'is_active' => true,
            'exam_type' => 'periodic',
        ]);

        // Создаем итоговый экзамен
        $finalExam = Test::create([
            'title' => 'Итоговый экзамен - ' . ($schedule->subject ? $schedule->subject->name : 'Предмет'),
            'description' => 'Итоговый экзамен для ' . ($schedule->group ? $schedule->group->name : 'группы'),
            'schedule_id' => $schedule->id,
            'created_by' => $teacher->id,
            'time_limit' => 120, // 120 минут
            'passing_score' => 60, // 60%
            'max_attempts' => 1, // Только одна попытка для итогового экзамена
            'is_active' => true,
            'exam_type' => 'final',
        ]);

        $this->info('✓ Создан периодический экзамен 1 (ID: ' . $periodicExam1->id . ')');
        $this->info('✓ Создан периодический экзамен 2 (ID: ' . $periodicExam2->id . ')');
        $this->info('✓ Создан итоговый экзамен (ID: ' . $finalExam->id . ')');
        
        $this->newLine();
        $this->info('Все экзамены успешно созданы!');
        $this->info('Теперь вы можете добавить вопросы к экзаменам через интерфейс учителя.');

        return 0;
    }
}
