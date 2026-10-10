<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Показать расписание группы текущего студента
     */
    public function index(Request $request)
    {
        $student = Auth::user();
        
        // Проверяем, что пользователь является студентом
        if (!$student->isStudent()) {
            abort(403, 'Доступ запрещен');
        }

        // Получаем группу студента
        $group = $student->group;
        
        if (!$group) {
            return spa('Schedule/Index', [
                'group' => null,
                'schedules' => [],
                'upcomingLessons' => [],
                'events' => [],
                'message' => 'Вы не привязаны ни к одной группе. Обратитесь к администратору для привязки к группе.'
            ]);
        }

        // Получаем расписание группы
        $schedules = Schedule::where('group_id', $group->id)
            ->where('is_active', true)
            ->with(['subject', 'teacher', 'lessons' => function($query) {
                $query->orderBy('lesson_schedule.order');
            }])
            ->orderBy('created_at')
            ->get();

        // Формируем события для календаря
        $events = $schedules->map(function($schedule) {
            // Если есть scheduled_at, используем его, иначе создаем фиктивное время
            $startTime = $schedule->scheduled_at ? 
                $schedule->scheduled_at->format('Y-m-d H:i') : 
                now()->addDays($schedule->id % 7)->format('Y-m-d 09:00');
            
            $endTime = $schedule->scheduled_at ? 
                $schedule->scheduled_at->addMinutes(90)->format('Y-m-d H:i') : 
                now()->addDays($schedule->id % 7)->format('Y-m-d 10:30');
            
            return [
                'id' => $schedule->id,
                'name' => $schedule->subject->name ?? 'Предмет не указан',
                'start' => $startTime,
                'end' => $endTime,
                'color' => $this->getEventColor($schedule),
                'details' => [
                    'teacher' => $schedule->teacher->full_name ?? 'Преподаватель не указан',
                    'room' => $schedule->lessons->first()->pivot->room ?? 'Аудитория не указана',
                    'semester' => $schedule->semester,
                    'credits' => $schedule->credits
                ]
            ];
        })->values();

        // Получаем ближайшие занятия (показываем все активные расписания)
        $upcomingLessons = $schedules->take(5)->map(function($schedule) {
            $lesson = $schedule->lessons->first();
            $scheduledTime = $schedule->scheduled_at ? $schedule->scheduled_at : now()->addDays($schedule->id % 7)->setTime(9, 0);
            
            return [
                'id' => $schedule->id,
                'course' => $schedule->subject->name ?? 'Предмет не указан',
                'time' => $scheduledTime->format('l, H:i'),
                'room' => $lesson ? $lesson->pivot->room : 'Аудитория не указана',
                'teacher' => $schedule->teacher->full_name ?? 'Преподаватель не указан',
                'type' => $lesson ? $lesson->type : 'Занятие',
                'scheduled_at' => $scheduledTime
            ];
        })->sortBy('scheduled_at')->values();

        return spa('Schedule/Index', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'full_name' => $group->full_name,
                'course' => $group->course,
                'specialty' => $group->specialty ? $group->specialty->name : null
            ],
            'schedules' => $schedules->map(function($schedule) {
                $lesson = $schedule->lessons->first();
                $scheduledTime = $schedule->scheduled_at ? $schedule->scheduled_at : now()->addDays($schedule->id % 7)->setTime(9, 0);
                
                return [
                    'id' => $schedule->id,
                    'subject_name' => $schedule->subject->name ?? 'Предмет не указан',
                    'teacher_name' => $schedule->teacher->full_name ?? 'Преподаватель не указан',
                    'scheduled_at' => $scheduledTime,
                    'semester' => $schedule->semester,
                    'credits' => $schedule->credits,
                    'study_year' => $schedule->study_year,
                    'room' => $lesson ? $lesson->pivot->room : null,
                    'start_time' => $lesson ? $lesson->pivot->start_time : '09:00',
                    'end_time' => $lesson ? $lesson->pivot->end_time : '10:30',
                    'lesson_title' => $lesson ? $lesson->title : null,
                    'lesson_type' => $lesson ? $lesson->type : 'Занятие',
                    'notes' => $lesson ? $lesson->pivot->notes : null
                ];
            }),
            'upcomingLessons' => $upcomingLessons,
            'events' => $events
        ]);
    }

    /**
     * Получить цвет события для календаря
     */
    private function getEventColor($schedule)
    {
        $colors = ['primary', 'secondary', 'success', 'warning', 'error', 'info'];
        $index = $schedule->id % count($colors);
        return $colors[$index];
    }
}

