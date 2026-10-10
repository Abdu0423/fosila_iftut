<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')
            ->paginate(10);

        return spa('Admin/Departments/Index', [
            'departments' => $departments,
            'filters' => [],
        ]);
    }

    /**
     * Обработка фильтров через POST запрос
     */
    public function filter(Request $request)
    {
        $departments = Department::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->when($request->filled('sortBy'), function ($query) use ($request) {
                $sortBy = $request->get('sortBy');
                $sortDesc = $request->boolean('sortDesc', false);
                $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->paginate($request->get('per_page', 10));

        return spa('Admin/Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return spa('Admin/Departments/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Кафедра успешно создана');
    }

    public function show(Department $department)
    {
        $department->load(['lessons', 'teachers']);

        return spa('Admin/Departments/Show', [
            'department' => $department,
        ]);
    }

    public function edit(Department $department)
    {
        return spa('Admin/Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Кафедра успешно обновлена');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Кафедра успешно удалена');
    }
}
