<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Inertia\Inertia;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->with('role');

        // Получаем фильтры из сессии или запроса
        $search = $request->get('search', session('teachers_search'));
        $departmentId = $request->get('department_id', session('teachers_department_id'));
        $status = $request->get('status', session('teachers_status'));
        $page = $request->get('page', 1);
        $sortBy = $request->get('sortBy', session('teachers_sort_by', 'name'));
        $sortDesc = $request->boolean('sortDesc', session('teachers_sort_desc', false));

        // Сохраняем фильтры в сессии
        session(['teachers_search' => $search]);
        session(['teachers_department_id' => $departmentId]);
        session(['teachers_status' => $status]);
        session(['teachers_sort_by' => $sortBy]);
        session(['teachers_sort_desc' => $sortDesc]);

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

        $teachers = $query->paginate(15);

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'departments' => \App\Models\Department::all(),
            'filters' => [
                'search' => $search,
                'department_id' => $departmentId,
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
            $q->where('name', 'teacher');
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
        $teachers = $query->paginate($perPage);

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'departments' => \App\Models\Department::all(),
            'filters' => $request->only(['search', 'department_id', 'status']),
        ]);
    }

    /**
     * Очистка фильтров
     */
    public function clearFilters()
    {
        session()->forget(['teachers_search', 'teachers_department_id', 'teachers_status', 'teachers_sort_by', 'teachers_sort_desc']);
        return redirect()->route('admin.teachers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.teachers.create');
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

        $teacherRole = Role::where('name', 'teacher')->first();

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $teacherRole->id,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Учитель создан успешно!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $teacher)
    {
        return view('admin.teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $teacher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $teacher->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $teacher->update($data);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Учитель обновлен успешно!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $teacher)
    {
        $teacher->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Учитель удален успешно!');
    }
}
