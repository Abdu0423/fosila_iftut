<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::whereHas('role', function($q) {
            $q->where('name', 'student');
        })->with('role');

        // Получаем фильтры из сессии или запроса
        $search = $request->get('search', session('students_search'));
        $groupId = $request->get('group_id', session('students_group_id'));
        $course = $request->get('course', session('students_course'));
        $status = $request->get('status', session('students_status'));
        $page = $request->get('page', 1);
        $sortBy = $request->get('sortBy', session('students_sort_by', 'name'));
        $sortDesc = $request->boolean('sortDesc', session('students_sort_desc', false));

        // Сохраняем фильтры в сессии
        session(['students_search' => $search]);
        session(['students_group_id' => $groupId]);
        session(['students_course' => $course]);
        session(['students_status' => $status]);
        session(['students_sort_by' => $sortBy]);
        session(['students_sort_desc' => $sortDesc]);

        // Поиск по ФИО, email, телефону
        if ($search) {
            // Заменяем пробелы на % для поиска по полному ФИО
            $searchTerm = str_replace(' ', '%', trim($search));
            
            $query->where(function($q) use ($search, $searchTerm) {
                // Поиск по отдельным полям
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
                
                // Поиск по полному ФИО (с учетом пробелов как %)
                if ($searchTerm !== $search) {
                    $q->orWhereRaw("CONCAT(COALESCE(name, ''), ' ', COALESCE(last_name, ''), ' ', COALESCE(middle_name, '')) LIKE ?", ["%{$searchTerm}%"]);
                }
            });
        }

        // Фильтр по статусу
        if ($status) {
            if ($status === 'active') {
                $query->where('email_verified_at', '!=', null);
            } else {
                $query->where('email_verified_at', null);
            }
        }

        // Сортировка
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        $students = $query->paginate(15);

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'groups' => \App\Models\Group::all(),
            'filters' => [
                'search' => $search,
                'group_id' => $groupId,
                'course' => $course,
                'status' => $status,
                'sortBy' => $sortBy,
                'sortDesc' => $sortDesc
            ],
        ]);
    }

    /**
     * Обработка фильтров через POST запрос
     */
    public function filter(Request $request)
    {
        $query = User::whereHas('role', function($q) {
            $q->where('name', 'student');
        })->with('role');

        // Поиск по ФИО, email, телефону
        if ($request->filled('search')) {
            $search = $request->get('search');
            // Заменяем пробелы на % для поиска по полному ФИО
            $searchTerm = str_replace(' ', '%', trim($search));
            
            $query->where(function($q) use ($search, $searchTerm) {
                // Поиск по отдельным полям
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
                
                // Поиск по полному ФИО (с учетом пробелов как %)
                if ($searchTerm !== $search) {
                    $q->orWhereRaw("CONCAT(COALESCE(name, ''), ' ', COALESCE(last_name, ''), ' ', COALESCE(middle_name, '')) LIKE ?", ["%{$searchTerm}%"]);
                }
            });
        }

        // Фильтр по статусу
        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('email_verified_at', '!=', null);
            } else {
                $query->where('email_verified_at', null);
            }
        }

        // Сортировка
        if ($request->filled('sortBy')) {
            $sortBy = $request->get('sortBy');
            $sortDesc = $request->boolean('sortDesc', false);
            $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        } else {
            $query->orderBy('name');
        }

        // Пагинация
        $perPage = $request->get('per_page', 15);
        $students = $query->paginate($perPage);

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'groups' => \App\Models\Group::all(),
            'filters' => $request->only(['search', 'group_id', 'course', 'status']),
        ]);
    }

    /**
     * Очистка фильтров
     */
    public function clearFilters()
    {
        session()->forget(['students_search', 'students_group_id', 'students_course', 'students_status', 'students_sort_by', 'students_sort_desc']);
        return redirect()->route('admin.students.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $studentRole = Role::where('name', 'student')->first();

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $studentRole->id,
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Студент создан успешно!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $student)
    {
        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $student->update($data);

        return redirect()->route('admin.students.index')
            ->with('success', 'Студент обновлен успешно!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Студент удален успешно!');
    }
}
