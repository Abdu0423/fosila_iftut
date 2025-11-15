<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments for the student.
     */
    public function index(Request $request)
    {
        $student = Auth::user();
        
        // Получаем задания для группы студента
        $query = Assignment::with(['lesson', 'schedule', 'group', 'teacher'])
            ->where('group_id', $student->group_id)
            ->where('status', 'published');

        // Поиск
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Фильтр по статусу выполнения
        if ($request->filled('submission_status')) {
            $status = $request->submission_status;
            if ($status === 'submitted') {
                $query->whereHas('submissions', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                });
            } elseif ($status === 'not_submitted') {
                $query->whereDoesntHave('submissions', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                });
            }
        }

        // Фильтр по сроку
        if ($request->filled('due_filter')) {
            $filter = $request->due_filter;
            if ($filter === 'overdue') {
                $query->where('due_date', '<', now())
                    ->whereDoesntHave('submissions', function($q) use ($student) {
                        $q->where('student_id', $student->id);
                    });
            } elseif ($filter === 'upcoming') {
                $query->where('due_date', '>', now())
                    ->where('due_date', '<=', now()->addDays(7));
            }
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $assignments = $query->paginate(20)->withQueryString();

        // Добавляем информацию о выполнении для каждого задания
        $assignments->getCollection()->transform(function($assignment) use ($student) {
            $submission = $assignment->submissions()
                ->where('student_id', $student->id)
                ->first();
            
            $assignment->submission = $submission;
            $assignment->is_submitted = $submission !== null;
            $assignment->is_overdue = $assignment->due_date < now() && !$assignment->is_submitted;
            
            return $assignment;
        });

        // Статистика
        $stats = [
            'total' => Assignment::where('group_id', $student->group_id)
                ->where('status', 'published')
                ->count(),
            'submitted' => Submission::where('student_id', $student->id)->count(),
            'pending' => Assignment::where('group_id', $student->group_id)
                ->where('status', 'published')
                ->whereDoesntHave('submissions', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                ->count(),
            'overdue' => Assignment::where('group_id', $student->group_id)
                ->where('status', 'published')
                ->where('due_date', '<', now())
                ->whereDoesntHave('submissions', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                ->count()
        ];

        return Inertia::render('Assignments/Index', [
            'assignments' => $assignments,
            'stats' => $stats,
            'filters' => $request->only(['search', 'submission_status', 'due_filter', 'sort_by', 'sort_order'])
        ]);
    }

    /**
     * Display the specified assignment.
     */
    public function show(Assignment $assignment)
    {
        $student = Auth::user();

        // Проверяем доступ
        if ($assignment->group_id !== $student->group_id) {
            abort(403, 'У вас нет доступа к этому заданию');
        }

        $assignment->load(['lesson', 'schedule', 'group', 'teacher']);

        // Получаем submission студента, если есть
        $submission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->first();

        $assignment->submission = $submission;
        $assignment->is_submitted = $submission !== null;
        $assignment->is_overdue = $assignment->due_date < now() && !$assignment->is_submitted;
        $assignment->can_submit = $assignment->due_date >= now() && !$assignment->is_submitted;

        return Inertia::render('Assignments/Show', [
            'assignment' => $assignment
        ]);
    }

    /**
     * Submit the assignment.
     */
    public function submit(Request $request, Assignment $assignment)
    {
        $student = Auth::user();

        // Проверяем доступ
        if ($assignment->group_id !== $student->group_id) {
            abort(403, 'У вас нет доступа к этому заданию');
        }

        // Проверяем, не подано ли уже задание
        $existingSubmission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            return back()->with('error', 'Вы уже подали это задание');
        }

        // Проверяем срок
        if ($assignment->due_date < now()) {
            return back()->with('error', 'Срок выполнения задания истек');
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max
            'comment' => 'nullable|string'
        ]);

        // Обработка файла
        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('submissions', 'public');
        }

        $validated['assignment_id'] = $assignment->id;
        $validated['student_id'] = $student->id;
        $validated['submitted_at'] = now();
        $validated['status'] = 'submitted';

        Submission::create($validated);

        return redirect()->route('student.assignments.show', $assignment)
            ->with('success', 'Задание успешно подано');
    }

    /**
     * Update the submission (if allowed).
     */
    public function updateSubmission(Request $request, Assignment $assignment, Submission $submission)
    {
        $student = Auth::user();

        // Проверяем доступ
        if ($submission->student_id !== $student->id) {
            abort(403, 'У вас нет доступа к этой работе');
        }

        // Проверяем, можно ли редактировать (например, если не проверена)
        if ($submission->status === 'graded') {
            return back()->with('error', 'Эта работа уже проверена и не может быть изменена');
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'comment' => 'nullable|string'
        ]);

        // Обработка файла
        if ($request->hasFile('file')) {
            // Удаляем старый файл
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('submissions', 'public');
        }

        $submission->update($validated);

        return back()->with('success', 'Работа успешно обновлена');
    }
}

