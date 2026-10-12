<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('faculty:id,name')
            ->withCount(['groups', 'specialties', 'subjects'])
            ->orderBy('name')
            ->get();

        return spa('Admin/Departments/Index', [
            'departments' => $departments,
            'faculties' => Faculty::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        return spa('Admin/Departments/Create', [
            'faculties' => Faculty::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDepartment($request);
        $validated['code'] = $this->uniqueCode($validated['code'] ?? null);
        $validated['is_active'] = $request->boolean('is_active', true);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Кафедра сохранена');
    }

    public function show(Department $department)
    {
        $department->load([
            'faculty:id,name',
            'specialties:id,department_id,name,code',
            'groups:id,department_id,name,status',
            'subjects:id,department_id,name,is_active',
        ]);
        $department->loadCount(['groups', 'specialties', 'subjects']);

        return spa('Admin/Departments/Show', [
            'department' => $department,
        ]);
    }

    public function edit(Department $department)
    {
        return spa('Admin/Departments/Edit', [
            'department' => $department,
            'faculties' => Faculty::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $this->validateDepartment($request, $department->id);
        $validated['code'] = $this->uniqueCode($validated['code'] ?? null, $department->id);
        $validated['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : $department->is_active;

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Кафедра сохранена');
    }

    public function toggleStatus(Department $department)
    {
        $department->update(['is_active' => ! $department->is_active]);

        return back()->with('success', 'Статус кафедры изменён');
    }

    public function destroy(Department $department)
    {
        if ($department->groups()->exists() || $department->specialties()->exists() || $department->subjects()->exists()) {
            return back()->with('error', 'Кафедру нельзя удалить: к ней привязаны группы, специальности или предметы');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Кафедра удалена');
    }

    private function validateDepartment(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'faculty_id' => $request->input('faculty_id') ?: null,
            'code' => $request->filled('code') ? trim((string) $request->input('code')) : null,
        ]);

        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('departments', 'code')->ignore($ignoreId),
            ],
            'description' => 'nullable|string|max:2000',
            'faculty_id' => 'nullable|exists:faculties,id',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    private function uniqueCode(?string $code, ?int $ignoreId = null): string
    {
        $code = strtoupper(trim((string) $code));
        if ($code !== '') {
            return $code;
        }

        $number = 1;
        do {
            $candidate = 'K'.$number;
            $number++;
            $exists = DB::table('departments')
                ->where('code', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists();
        } while ($exists);

        return $candidate;
    }
}
