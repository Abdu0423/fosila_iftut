<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::withCount('departments')
            ->orderBy('name')
            ->get();

        return spa('Admin/Faculties/Index', [
            'faculties' => $faculties,
        ]);
    }

    public function create()
    {
        return spa('Admin/Faculties/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateFaculty($request);
        $validated['code'] = $this->uniqueCode($validated['code'] ?? null);
        $validated['is_active'] = $request->boolean('is_active', true);

        Faculty::create($validated);

        return redirect()->route('admin.faculties.index')
            ->with('success', 'Факультет сохранён');
    }

    public function show(Faculty $faculty)
    {
        $faculty->load(['departments' => function ($query) {
            $query->withCount(['groups', 'specialties', 'subjects'])->orderBy('name');
        }]);

        return spa('Admin/Faculties/Show', [
            'faculty' => $faculty,
        ]);
    }

    public function edit(Faculty $faculty)
    {
        return spa('Admin/Faculties/Edit', [
            'faculty' => $faculty,
        ]);
    }

    public function update(Request $request, Faculty $faculty)
    {
        $validated = $this->validateFaculty($request, $faculty->id);
        $validated['code'] = $this->uniqueCode($validated['code'] ?? null, $faculty->id);
        $validated['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : $faculty->is_active;

        $faculty->update($validated);

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
        ]);

        return $request->validate([
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
