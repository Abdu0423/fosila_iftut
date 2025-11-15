<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    /**
     * Display a listing of the student's grades.
     */
    public function index(Request $request)
    {
        $student = Auth::user();
        
        // Получаем оценки студента
        $query = Grade::with(['schedule.lesson', 'schedule.teacher', 'student'])
            ->where('student_id', $student->id);

        // Фильтр по семестру
        if ($request->filled('semester')) {
            $query->whereHas('schedule', function($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        // Фильтр по году обучения
        if ($request->filled('study_year')) {
            $query->whereHas('schedule', function($q) use ($request) {
                $q->where('study_year', $request->study_year);
            });
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $grades = $query->paginate(20)->withQueryString();

        // Статистика
        $stats = [
            'total_grades' => Grade::where('student_id', $student->id)->count(),
            'average_grade' => Grade::where('student_id', $student->id)->avg('grade'),
            'highest_grade' => Grade::where('student_id', $student->id)->max('grade'),
            'lowest_grade' => Grade::where('student_id', $student->id)->min('grade'),
        ];

        // Оценки по семестрам
        $gradesBySemester = Grade::where('student_id', $student->id)
            ->with('schedule')
            ->get()
            ->groupBy(function($grade) {
                return $grade->schedule->semester ?? 'unknown';
            })
            ->map(function($grades) {
                return [
                    'count' => $grades->count(),
                    'average' => $grades->avg('grade')
                ];
            });

        // Получаем список семестров и годов для фильтров
        $semesters = Schedule::whereHas('grades', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })->distinct()->pluck('semester')->sort()->values();

        $studyYears = Schedule::whereHas('grades', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })->distinct()->pluck('study_year')->sort()->values();

        return Inertia::render('Grades/Index', [
            'grades' => $grades,
            'stats' => $stats,
            'gradesBySemester' => $gradesBySemester,
            'semesters' => $semesters,
            'studyYears' => $studyYears,
            'filters' => $request->only(['semester', 'study_year', 'sort_by', 'sort_order'])
        ]);
    }

    /**
     * Display the specified grade details.
     */
    public function show(Grade $grade)
    {
        // Проверяем, что оценка принадлежит текущему студенту
        if ($grade->student_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этой оценке');
        }

        $grade->load(['schedule.lesson', 'schedule.teacher', 'student']);

        return Inertia::render('Grades/Show', [
            'grade' => $grade
        ]);
    }
}

