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
        // Сначала пробуем получить через pivot-таблицу group_student
        $groupStudents = $schedule->group->students;
        
        // Если через pivot пусто, получаем напрямую через group_id
        if ($groupStudents->isEmpty()) {
            $groupStudents = User::where('group_id', $schedule->group_id)
                ->whereHas('role', function ($q) {
                    $q->where('slug', 'student');
                })
                ->get();
        }
        
        $students = $groupStudents->map(function ($student) use ($schedule) {
            // Получаем или создаем запись оценок
            $grade = Grade::firstOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'student_id' => $student->id,
                ],
                [
                    'rating_teacher_1' => null,
                    'rating_teacher_2' => null,
                    'rating_test_1' => null,
                    'rating_test_2' => null,
                    'final_exam_grade' => null,
                    'final_exam_type' => null,
                ]
            );

            // Получаем оценки из тестов (rating_test_1, rating_test_2, final_exam_grade)
            $testGrades = $this->getTestGrades($schedule, $student);
            
            // Обновляем оценки из тестов, если они изменились
            $updated = false;
            if ($grade->rating_test_1 != $testGrades['rating_test_1']) {
                $grade->rating_test_1 = $testGrades['rating_test_1'];
                $updated = true;
            }
            if ($grade->rating_test_2 != $testGrades['rating_test_2']) {
                $grade->rating_test_2 = $testGrades['rating_test_2'];
                $updated = true;
            }
            if ($grade->final_exam_grade != $testGrades['final_exam_grade']) {
                $grade->final_exam_grade = $testGrades['final_exam_grade'];
                $grade->final_exam_type = $testGrades['final_exam_type'];
                $updated = true;
            }
            
            if ($updated) {
                $grade->updateFinalGrades();
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'grade_id' => $grade->id,
                'rating_teacher_1' => $grade->rating_teacher_1 ? (float)$grade->rating_teacher_1 : null,
                'rating_teacher_2' => $grade->rating_teacher_2 ? (float)$grade->rating_teacher_2 : null,
                'rating_test_1' => $grade->rating_test_1 ? (float)$grade->rating_test_1 : null,
                'rating_test_2' => $grade->rating_test_2 ? (float)$grade->rating_test_2 : null,
                'final_exam_grade' => $grade->final_exam_grade ? (float)$grade->final_exam_grade : null,
                'final_exam_type' => $grade->final_exam_type,
                'final_grade_100' => $grade->final_grade_100 ? (float)$grade->final_grade_100 : null,
                'final_grade_10' => $grade->final_grade_10 ? (float)$grade->final_grade_10 : null,
                'final_grade_letter' => $grade->final_grade_letter,
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
            'rating_teacher_1' => 'nullable|numeric|min:0|max:100',
            'rating_teacher_2' => 'nullable|numeric|min:0|max:100',
        ]);

        // Обновляем только оценки, которые может ставить учитель (rating_teacher_1 и rating_teacher_2)
        $grade->update([
            'rating_teacher_1' => $request->rating_teacher_1,
            'rating_teacher_2' => $request->rating_teacher_2,
        ]);

        // Обновляем итоговые оценки
        $grade->updateFinalGrades();

        return response()->json([
            'success' => true,
            'message' => 'Оценка обновлена',
            'grade' => $grade->fresh(),
        ]);
    }

    /**
     * Получить оценки из тестов
     * 
     * rating_test_1 и rating_test_2 - два рейтинговых теста (максимальные из 3 попыток)
     * final_exam_grade - последний экзамен (максимальный из 3 попыток для теста)
     */
    private function getTestGrades(Schedule $schedule, User $student)
    {
        // Получаем все тесты для этого расписания
        $tests = Test::where('schedule_id', $schedule->id)->get();
        
        if ($tests->isEmpty()) {
            return [
                'rating_test_1' => null,
                'rating_test_2' => null,
                'final_exam_grade' => null,
                'final_exam_type' => null,
            ];
        }

        // Разделяем тесты на рейтинговые и итоговые
        $ratingTests = $tests->where('exam_type', 'rating')->sortBy('id');
        $finalTest = $tests->where('exam_type', 'final')->first();

        // rating_test_1 - максимальный результат первого рейтингового теста (из 3 попыток)
        $firstRatingTest = $ratingTests->first();
        $rating_test_1 = null;
        if ($firstRatingTest) {
            $attempts = TestAttempt::where('test_id', $firstRatingTest->id)
                ->where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->orderBy('score', 'desc')
                ->limit(3)
                ->get();
            $rating_test_1 = $attempts->isNotEmpty() ? (float)$attempts->max('score') : null;
        }

        // rating_test_2 - максимальный результат второго рейтингового теста (из 3 попыток)
        $secondRatingTest = $ratingTests->skip(1)->first();
        $rating_test_2 = null;
        if ($secondRatingTest) {
            $attempts = TestAttempt::where('test_id', $secondRatingTest->id)
                ->where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->orderBy('score', 'desc')
                ->limit(3)
                ->get();
            $rating_test_2 = $attempts->isNotEmpty() ? (float)$attempts->max('score') : null;
        }

        // final_exam_grade - максимальный результат итогового экзамена (из 3 попыток для теста)
        $final_exam_grade = null;
        $final_exam_type = null;
        
        if ($finalTest) {
            $attempts = TestAttempt::where('test_id', $finalTest->id)
                ->where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->orderBy('score', 'desc')
                ->limit(3)
                ->get();
            $final_exam_grade = $attempts->isNotEmpty() ? (float)$attempts->max('score') : null;
            $final_exam_type = 'test';
        }

        return [
            'rating_test_1' => $rating_test_1,
            'rating_test_2' => $rating_test_2,
            'final_exam_grade' => $final_exam_grade,
            'final_exam_type' => $final_exam_type,
        ];
    }
}
