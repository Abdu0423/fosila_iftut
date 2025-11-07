<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Schedule;
use App\Models\Grade;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    /**
     * Показать страницу оценок
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Получаем расписания учителя с предметами и группами
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['subject', 'group'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'subject_name' => $schedule->subject ? $schedule->subject->name : 'Не указан',
                    'group_name' => $schedule->group ? $schedule->group->name : 'Не указана',
                    'semester' => $schedule->semester,
                    'study_year' => $schedule->study_year,
                ];
            });

        return Inertia::render('Teacher/Grades/Index', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Получить оценки для выбранного расписания
     */
    public function getGrades(Request $request, Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем доступ
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        // Загружаем группу
        $schedule->load(['group']);
        
        if (!$schedule->group) {
            return response()->json([
                'success' => false,
                'message' => 'Группа не найдена'
            ], 404);
        }

        // Получаем студентов группы
        $groupStudents = $schedule->group->students;
        
        $students = $groupStudents->map(function ($student) use ($schedule) {
            // Получаем или создаем запись оценок
            $grade = Grade::firstOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'student_id' => $student->id,
                ],
                [
                    'grade_1' => null,
                    'grade_2' => null,
                    'grade_3' => null,
                    'grade_4' => null,
                    'grade_5' => null,
                ]
            );

            // Получаем оценки из тестов (grade_3, grade_4, grade_5)
            $testGrades = $this->getTestGrades($schedule, $student);
            
            // Обновляем оценки из тестов, если они изменились
            $updated = false;
            if ($grade->grade_3 != $testGrades['grade_3']) {
                $grade->grade_3 = $testGrades['grade_3'];
                $updated = true;
            }
            if ($grade->grade_4 != $testGrades['grade_4']) {
                $grade->grade_4 = $testGrades['grade_4'];
                $updated = true;
            }
            if ($grade->grade_5 != $testGrades['grade_5']) {
                $grade->grade_5 = $testGrades['grade_5'];
                $updated = true;
            }
            
            if ($updated) {
                $grade->save();
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'grade_id' => $grade->id,
                'grade_1' => $grade->grade_1 ? (float)$grade->grade_1 : null,
                'grade_2' => $grade->grade_2 ? (float)$grade->grade_2 : null,
                'grade_3' => $grade->grade_3 ? (float)$grade->grade_3 : null,
                'grade_4' => $grade->grade_4 ? (float)$grade->grade_4 : null,
                'grade_5' => $grade->grade_5 ? (float)$grade->grade_5 : null,
            ];
        });

        return response()->json([
            'success' => true,
            'schedule' => [
                'id' => $schedule->id,
                'subject_name' => $schedule->subject ? $schedule->subject->name : 'Не указан',
                'group_name' => $schedule->group->name,
            ],
            'students' => $students,
        ]);
    }

    /**
     * Обновить оценку
     */
    public function updateGrade(Request $request, Grade $grade)
    {
        $teacher = Auth::user();
        
        // Проверяем доступ
        if ($grade->schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этой оценке');
        }

        $request->validate([
            'grade_1' => 'nullable|numeric|min:0|max:100',
            'grade_2' => 'nullable|numeric|min:0|max:100',
        ]);

        // Обновляем только оценки, которые может ставить учитель (grade_1 и grade_2)
        $grade->update([
            'grade_1' => $request->grade_1,
            'grade_2' => $request->grade_2,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Оценка обновлена',
            'grade' => $grade->fresh(),
        ]);
    }

    /**
     * Получить оценки из тестов
     * 
     * grade_3 и grade_4 - два лучших результата периодических экзаменов
     * grade_5 - лучший результат итогового экзамена
     */
    private function getTestGrades(Schedule $schedule, User $student)
    {
        // Получаем все тесты для этого расписания
        $tests = Test::where('schedule_id', $schedule->id)->get();
        
        if ($tests->isEmpty()) {
            return [
                'grade_3' => null,
                'grade_4' => null,
                'grade_5' => null,
            ];
        }

        // Разделяем тесты на периодические и итоговые
        $periodicTests = $tests->where('exam_type', 'periodic')->sortBy('id');
        $finalTest = $tests->where('exam_type', 'final')->first();

        // grade_3 - лучший результат первого периодического экзамена
        $firstPeriodicTest = $periodicTests->first();
        $grade_3 = null;
        if ($firstPeriodicTest) {
            $bestAttempt = TestAttempt::where('test_id', $firstPeriodicTest->id)
                ->where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->orderBy('score', 'desc')
                ->first();
            $grade_3 = $bestAttempt ? (float)$bestAttempt->score : null;
        }

        // grade_4 - лучший результат второго периодического экзамена
        $secondPeriodicTest = $periodicTests->skip(1)->first();
        $grade_4 = null;
        if ($secondPeriodicTest) {
            $bestAttempt = TestAttempt::where('test_id', $secondPeriodicTest->id)
                ->where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->orderBy('score', 'desc')
                ->first();
            $grade_4 = $bestAttempt ? (float)$bestAttempt->score : null;
        }

        // Получаем попытки студента по итоговому экзамену
        $finalAttempts = collect([]);
        if ($finalTest) {
            $finalAttempts = TestAttempt::where('test_id', $finalTest->id)
                ->where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->orderBy('score', 'desc')
                ->get();
        }

        // grade_5 - лучший результат итогового экзамена
        $bestFinalAttempt = $finalAttempts->first();
        $grade_5 = $bestFinalAttempt ? (float)$bestFinalAttempt->score : null;

        return [
            'grade_3' => $grade_3,
            'grade_4' => $grade_4,
            'grade_5' => $grade_5,
        ];
    }
}
