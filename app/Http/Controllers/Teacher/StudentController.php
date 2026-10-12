<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        
        // Получаем все группы, где преподает учитель
        $groups = Group::whereHas('schedules', function($query) use ($teacher) {
            $query->when(! $teacher->seesAllTeachingRecords(), fn ($q) => $q->where('teacher_id', $teacher->id));
        })->orderBy('name')->get();

        return spa('Teacher/Students/Index', [
            'groups' => $groups->map(function($group) {
                // Получаем студентов этой группы через прямое отношение
                $students = User::where('group_id', $group->id)
                    ->whereHas('role', function($query) {
                        $query->where('name', 'student');
                    })
                    ->orderBy('name')
                    ->get();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'full_name' => $group->full_name,
                    'students_count' => $students->count(),
                    'students' => $students->map(function($student) {
                        return [
                            'id' => $student->id,
                            'name' => $student->name,
                            'last_name' => $student->last_name,
                            'middle_name' => $student->middle_name,
                            'full_name' => trim($student->last_name . ' ' . $student->name . ' ' . $student->middle_name),
                            'email' => $student->email,
                            'phone' => $student->phone,
                        ];
                    })
                ];
            })
        ]);
    }

    public function show(Group $group)
    {
        $teacher = Auth::user();
        
        // Проверяем, что учитель преподает в этой группе
        $hasAccess = Schedule::forTeachingUser($teacher)
            ->where('group_id', $group->id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'У вас нет доступа к этой группе');
        }

        // Получаем студентов группы
        $students = $group->students()
            ->whereHas('role', function($query) {
                $query->where('name', 'student');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'last_name', 'middle_name', 'email', 'phone', 'address']);

        // Получаем расписание уроков для этой группы
        $schedules = Schedule::forTeachingUser($teacher)
            ->where('group_id', $group->id)
            ->with(['lesson', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'lesson_title' => $schedule->lesson->title ?? 'Урок без названия',
                    'subject_name' => $schedule->subject->name ?? 'Не указан',
                    'day_of_week' => $schedule->day_of_week ?? '',
                    'start_time' => $schedule->start_time ?? '',
                    'end_time' => $schedule->end_time ?? '',
                    'room' => $schedule->room ?? '',
                    'semester' => $schedule->semester ?? '',
                    'is_active' => $schedule->is_active ?? true,
                ];
            });

        return spa('Teacher/Students/Show', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'full_name' => $group->full_name,
                'enrollment_year' => $group->enrollment_year,
                'graduation_year' => $group->graduation_year,
                'course' => $group->course,
                'status' => $group->status,
                'status_text' => $group->status_text,
            ],
            'students' => $students->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'last_name' => $student->last_name,
                    'middle_name' => $student->middle_name,
                    'full_name' => trim($student->last_name . ' ' . $student->name . ' ' . $student->middle_name),
                    'email' => $student->email,
                    'phone' => $student->phone,
                    'address' => $student->address,
                ];
            }),
            'schedules' => $schedules,
            'stats' => [
                'total_students' => $students->count(),
                'total_lessons' => $schedules->count(),
                'active_lessons' => $schedules->count(),
            ]
        ]);
    }

    public function showStudent(User $student)
    {
        $teacher = Auth::user();
        
        // Проверяем, что студент учится в группе, где преподает учитель
        $hasAccess = $student->group && 
            Schedule::where('group_id', $student->group->id)
                ->when(! $teacher->seesAllTeachingRecords(), fn ($q) => $q->where('teacher_id', $teacher->id))
                ->exists();
            
        if (!$hasAccess) {
            abort(403, 'У вас нет доступа к этому студенту');
        }

        // Получаем группу студента, где преподает учитель
        $group = $student->group;
        $schedules = Schedule::where('group_id', $group->id)
            ->when(! $teacher->seesAllTeachingRecords(), fn ($q) => $q->where('teacher_id', $teacher->id))
            ->with(['lesson', 'subject'])
            ->get();

        return spa('Teacher/Students/StudentShow', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'last_name' => $student->last_name,
                'middle_name' => $student->middle_name,
                'full_name' => trim($student->last_name . ' ' . $student->name . ' ' . $student->middle_name),
                'email' => $student->email,
                'phone' => $student->phone,
                'address' => $student->address,
            ],
            'groups' => [
                [
                    'id' => $group->id,
                    'name' => $group->name,
                    'full_name' => $group->full_name,
                    'schedules' => $schedules->map(function($schedule) {
                        return [
                            'id' => $schedule->id,
                            'lesson_title' => $schedule->lesson->title ?? 'Урок без названия',
                            'subject_name' => $schedule->subject->name ?? 'Не указан',
                            'day_of_week' => $schedule->day_of_week ?? '',
                            'start_time' => $schedule->start_time ?? '',
                            'end_time' => $schedule->end_time ?? '',
                            'room' => $schedule->room ?? '',
                            'semester' => $schedule->semester ?? '',
                            'is_active' => $schedule->is_active ?? true,
                        ];
                    })
                ]
            ]
        ]);
    }
}

