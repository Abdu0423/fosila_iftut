<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::with([
                'user:id,name,last_name,middle_name',
            ])
            ->orderBy('name')
            ->get();

        return spa('Admin/Faculties/Index', [
            'faculties' => $faculties,
            'users' => $this->staffUsers(),
        ]);
    }

    public function create()
    {
        return spa('Admin/Faculties/Create', [
            'users' => $this->staffUsers(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateFaculty($request);
        $departmentId = $validated['department_id'] ?? null;
        unset($validated['department_id']);
        $validated['code'] = $this->uniqueCode($validated['code'] ?? null);
        $validated['is_active'] = $request->boolean('is_active', true);

        $faculty = Faculty::create($validated);
        $this->attachDepartment($faculty, $departmentId);

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Факультет сохранён');
    }

    public function show(Faculty $faculty)
    {
        $faculty->load([
            'user:id,name,last_name,middle_name',
            'departments' => function ($query) {
                $query->withCount(['groups', 'specialties', 'subjects'])->orderBy('name');
            },
        ]);

        return spa('Admin/Faculties/Show', [
            'faculty' => $faculty,
        ]);
    }

    public function edit(Faculty $faculty)
    {
        $faculty->load([
            'departments:id,faculty_id,name',
            'user:id,name,last_name,middle_name',
        ]);

        return spa('Admin/Faculties/Edit', [
            'faculty' => $faculty,
            'users' => $this->staffUsers(),
        ]);
    }

    public function update(Request $request, Faculty $faculty)
    {
        $validated = $this->validateFaculty($request, $faculty->id);
        $departmentId = array_key_exists('department_id', $validated) ? $validated['department_id'] : null;
        $assignDepartment = array_key_exists('department_id', $validated);
        unset($validated['department_id']);
        $validated['code'] = $this->uniqueCode($validated['code'] ?? null, $faculty->id);
        $validated['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : $faculty->is_active;

        $faculty->update($validated);
        if ($assignDepartment) {
            $this->attachDepartment($faculty, $departmentId);
        }

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Факультет сохранён');
    }

    public function toggleStatus(Faculty $faculty)
    {
        $faculty->update(['is_active' => ! $faculty->is_active]);

        return back()->with('success', 'Статус факультета изменён');
    }

    public function destroy(Faculty $faculty)
    {
        if ($faculty->departments()->exists()) {
            return back()->with('error', 'Факультет нельзя удалить: к нему привязаны кафедры');
        }

        $faculty->delete();

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Факультет удалён');
    }

    private function validateFaculty(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'code' => $request->filled('code') ? trim((string) $request->input('code')) : null,
            'user_id' => $request->input('user_id') ?: null,
            'department_id' => $request->input('department_id') ?: null,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('faculties', 'code')->ignore($ignoreId),
            ],
            'description' => 'nullable|string|max:2000',
            'dean_name' => 'nullable|string|max:255',
            'dean_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $this->assertNotStudent(isset($validated['user_id']) ? (int) $validated['user_id'] : null);

        return $validated;
    }

    private function staffUsers()
    {
        return User::query()
            ->whereDoesntHave('role', function ($query) {
                $query->where('name', 'student');
            })
            ->orderBy('last_name')
            ->orderBy('name')
            ->get(['id', 'name', 'last_name', 'middle_name']);
    }

    private function attachDepartment(Faculty $faculty, $departmentId): void
    {
        if (! $departmentId) {
            return;
        }

        Department::where('id', $departmentId)->update(['faculty_id' => $faculty->id]);
    }

    private function assertNotStudent(?int $userId): void
    {
        if (! $userId) {
            return;
        }

        $isStudent = User::where('id', $userId)
            ->whereHas('role', function ($query) {
                $query->where('name', 'student');
            })
            ->exists();

        if ($isStudent) {
            throw ValidationException::withMessages([
                'user_id' => 'Нельзя выбрать студента',
            ]);
        }
    }

    private function uniqueCode(?string $code, ?int $ignoreId = null): string
    {
        $code = strtoupper(trim((string) $code));
        if ($code !== '') {
            return $code;
        }

        $number = 1;
        do {
            $candidate = 'F'.$number;
            $number++;
            $exists = DB::table('faculties')
                ->where('code', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists();
        } while ($exists);

        return $candidate;
    }
}
